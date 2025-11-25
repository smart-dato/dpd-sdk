<?php

namespace SmartDato\Dpd\Clients;

use SmartDato\Dpd\Auth\TokenManager;
use SoapHeader;

class ParcelLifeCycleServiceClient extends BaseSoapClient
{
    public function __construct(
        string $wsdlUrl,
        array $soapOptions,
        protected TokenManager $tokenManager
    ) {
        parent::__construct($wsdlUrl, $soapOptions);
    }

    /**
     * Build SOAP headers with authentication token.
     */
    protected function buildSoapHeaders(): array
    {
        $token = $this->tokenManager->getToken();
        $credentials = $this->tokenManager->getCredentials();

        return [
            new SoapHeader(
                'http://dpd.com/common/service/types/Authentication/2.0',
                'authentication',
                [
                    'delisId' => $credentials->delisId,
                    'authToken' => $token,
                    'messageLanguage' => 'de_DE',
                ]
            ),
        ];
    }

    /**
     * Get tracking data for a parcel.
     *
     * @param  string  $parcelNumber  The parcel number to track
     * @return mixed SOAP response containing tracking events
     */
    public function getTrackingData(string $parcelNumber): mixed
    {
        $params = [
            'parcelLabelNumber' => $parcelNumber,
        ];

        return $this->call('getTrackingData', [$params]);
    }
}
