<?php

namespace SmartDato\Dpd\Clients;

use SmartDato\Dpd\Exceptions\AuthenticationException;
use SmartDato\Dpd\Exceptions\RateLimitException;
use SmartDato\Dpd\Exceptions\SoapException;
use SmartDato\Dpd\Exceptions\ValidationException;
use SoapClient;
use SoapFault;

abstract class BaseSoapClient
{
    protected SoapClient $soapClient;

    protected array $soapOptions;

    public function __construct(
        protected string $wsdlUrl,
        array $soapOptions = []
    ) {
        $this->soapOptions = $soapOptions;
        $this->initializeSoapClient();
    }

    /**
     * Initialize the SOAP client with the WSDL URL and options.
     */
    protected function initializeSoapClient(): void
    {
        $this->soapClient = new SoapClient($this->wsdlUrl, $this->soapOptions);
    }

    /**
     * Call a SOAP method with the given parameters.
     */
    protected function call(string $method, array $params = []): mixed
    {
        try {
            // Set SOAP headers if needed
            $headers = $this->buildSoapHeaders();
            if (! empty($headers)) {
                $this->soapClient->__setSoapHeaders($headers);
            }

            // Make the SOAP call
            $response = $this->soapClient->__soapCall($method, $params);

            $this->logRequest($method, $params, $response);

            return $response;
        } catch (SoapFault $fault) {
            $this->logRequest($method, $params, null, $fault);

            // Check if this is a transient error and retry
            if ($this->isTransientError($fault)) {
                sleep(1);

                try {
                    $response = $this->soapClient->__soapCall($method, $params);
                    $this->logRequest($method, $params, $response, null, true);

                    return $response;
                } catch (SoapFault $retryFault) {
                    throw $this->handleSoapFault($retryFault);
                }
            }

            throw $this->handleSoapFault($fault);
        }
    }

    /**
     * Build SOAP headers for authenticated requests.
     * Override in subclasses if headers are needed.
     */
    protected function buildSoapHeaders(): array
    {
        return [];
    }

    /**
     * Handle SOAP faults and convert to custom exceptions.
     */
    protected function handleSoapFault(SoapFault $fault): \Throwable
    {
        $faultCode = $fault->faultcode ?? 'Unknown';
        $message = $fault->faultstring ?? 'Unknown SOAP error';

        // Extract DPD error code if available
        $dpdErrorCode = null;
        if (isset($fault->detail) && is_object($fault->detail)) {
            $dpdErrorCode = $fault->detail->errorCode ?? null;
        }

        return match (true) {
            str_contains($faultCode, 'AUTHENTICATION') || str_contains($message, 'authentication') => new AuthenticationException(
                message: 'DPD authentication failed: '.$message,
                code: $dpdErrorCode ?? 0,
                previous: $fault,
                context: ['fault_code' => $faultCode]
            ),
            str_contains($faultCode, 'RATE_LIMIT') || str_contains($message, '429') || str_contains($message, 'rate limit') => new RateLimitException(
                message: 'DPD rate limit exceeded: '.$message,
                code: $dpdErrorCode ?? 429,
                previous: $fault,
                context: ['fault_code' => $faultCode]
            ),
            str_contains($faultCode, 'VALIDATION') || str_contains($message, 'validation') => new ValidationException(
                message: 'DPD validation failed: '.$message,
                code: $dpdErrorCode ?? 0,
                previous: $fault,
                context: ['fault_code' => $faultCode]
            ),
            default => new SoapException(
                message: 'DPD SOAP error: '.$message,
                code: $dpdErrorCode ?? 0,
                previous: $fault,
                context: ['fault_code' => $faultCode]
            ),
        };
    }

    /**
     * Check if a SOAP fault is transient and should be retried.
     */
    protected function isTransientError(SoapFault $fault): bool
    {
        $transientCodes = ['HTTP', 'ETIMEDOUT', 'ECONNRESET'];
        $faultCode = $fault->faultcode ?? '';

        foreach ($transientCodes as $code) {
            if (str_contains($faultCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log SOAP requests and responses for debugging.
     */
    protected function logRequest(string $method, array $params, mixed $response = null, ?SoapFault $fault = null, bool $isRetry = false): void
    {
        // Only log if logging is enabled in config
        if (! config('dpd-sdk.logging.enabled', false)) {
            return;
        }

        $logData = [
            'method' => $method,
            'params' => $params,
            'is_retry' => $isRetry,
        ];

        if ($fault) {
            $logData['fault'] = [
                'code' => $fault->faultcode ?? 'Unknown',
                'message' => $fault->faultstring ?? 'Unknown error',
            ];

            logger()->channel(config('dpd-sdk.logging.channel', 'stack'))
                ->error('DPD SOAP Request Failed', $logData);
        } elseif ($response) {
            $logData['response'] = $response;

            logger()->channel(config('dpd-sdk.logging.channel', 'stack'))
                ->debug('DPD SOAP Request Success', $logData);
        }
    }

    /**
     * Get the last SOAP request XML (useful for debugging).
     */
    public function getLastRequest(): ?string
    {
        return $this->soapClient->__getLastRequest();
    }

    /**
     * Get the last SOAP response XML (useful for debugging).
     */
    public function getLastResponse(): ?string
    {
        return $this->soapClient->__getLastResponse();
    }
}
