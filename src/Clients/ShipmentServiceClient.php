<?php

namespace SmartDato\Dpd\Clients;

use SmartDato\Dpd\Auth\TokenManager;
use SoapHeader;

class ShipmentServiceClient extends BaseSoapClient
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
     * Store orders (create shipments and generate labels).
     *
     * @param  array  $printOptions  Print options for labels (format, paper size, etc.)
     * @param  array  $orders  Array of order/shipment data (max 30)
     * @return mixed SOAP response containing parcel numbers and labels
     *
     * @throws \Throwable
     */
    public function storeOrders(array $printOptions, array $orders): mixed
    {
        $params = [
            'printOptions' => $printOptions,
            'order' => $orders,
        ];

        return $this->call('storeOrders', [$params]);
    }
}
