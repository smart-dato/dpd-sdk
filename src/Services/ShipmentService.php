<?php

namespace SmartDato\Dpd\Services;

use SmartDato\Dpd\Clients\ShipmentServiceClient;
use SmartDato\Dpd\DTOs\Label;
use SmartDato\Dpd\DTOs\ShipmentResponse;

class ShipmentService
{
    public function __construct(
        protected ShipmentServiceClient $client,
        protected array $config = []
    ) {}

    /**
     * Create a single shipment.
     *
     * @param  array  $shipmentData  Shipment data including sender, recipient, parcels
     *
     * @throws \Throwable
     */
    public function createShipment(array $shipmentData): ShipmentResponse
    {
        // Get print options from config or shipment data
        $printOptions = $shipmentData['print_options'] ?? $this->getDefaultPrintOptions();

        // Build the order structure for DPD API
        $order = $this->buildOrder($shipmentData);

        // Call the SOAP service
        $response = $this->client->storeOrders($printOptions, [$order]);

        // Parse and return the response
        return $this->parseResponse($response);
    }

    /**
     * Build order structure from shipment data.
     */
    protected function buildOrder(array $shipmentData): array
    {
        $order = [
            'generalShipmentData' => $this->buildGeneralShipmentData($shipmentData),
            'parcels' => $shipmentData['parcels'] ?? [],
            'productAndServiceData' => $shipmentData['product_and_service_data'] ?? [
                'orderType' => 'consignment',
            ],
        ];

        return $order;
    }

    /**
     * Build general shipment data structure.
     */
    protected function buildGeneralShipmentData(array $shipmentData): array
    {

        $generalData = [
            'sendingDepot' => $shipmentData['sending_depot'] ?? null, // must be exactly 4 digits (depot code)
            'product' => $shipmentData['product'] ?? 'CL',
            'sender' => $this->normalizeAddress($shipmentData['sender']),
            'recipient' => $this->normalizeAddress($shipmentData['recipient']),
        ];

        // Add optional fields if present
        $optionalFields = [
            'mpsId',
            'cUser',
            'mpsCustomerReferenceNumber1',
            'mpsCustomerReferenceNumber2',
            'mpsCustomerReferenceNumber3',
            'mpsCustomerReferenceNumber4',
            'identificationNumber',
            'mpsCompleteDelivery',
            'mpsVolume',
            'mpsWeight',
            'returnAddress',
            'softwareVersion',
        ];

        foreach ($optionalFields as $field) {
            if (isset($shipmentData[$field])) {
                $generalData[$field] = $shipmentData[$field];
            }
        }

        return $generalData;
    }

    /**
     * Normalize address structure to match DPD API format.
     */
    protected function normalizeAddress(array $address): array
    {
        return [
            'name1' => $address['name'] ?? $address['name1'] ?? '',
            'name2' => $address['company'] ?? $address['name2'] ?? null,
            'street' => $address['street'] ?? '',
            'houseNo' => $address['houseNumber'] ?? $address['houseNo'] ?? '',
            'country' => $address['country'] ?? '',
            'zipCode' => $address['zipCode'] ?? '',
            'city' => $address['city'] ?? '',
            'state' => $address['state'] ?? null,
            'contact' => $address['contact'] ?? null,
            'phone' => $address['phone'] ?? null,
            'mobile' => $address['mobile'] ?? null,
            'fax' => $address['fax'] ?? null,
            'email' => $address['email'] ?? null,
            'comment' => $address['comment'] ?? null,
            'gln' => $address['gln'] ?? null,
            'customerNumber' => $address['customerNumber'] ?? null,
            'iaccount' => $address['iaccount'] ?? null,
            'businessUnit' => $address['businessUnit'] ?? null,
            'addressType' => $address['addressType'] ?? null,
            'additionalInfo2' => $address['additionalInfo2'] ?? null,
            'additionalInfo3' => $address['additionalInfo3'] ?? null,
            'eori' => $address['eori'] ?? null,
            'vatNumber' => $address['vatNumber'] ?? null,
            'taxIdType' => $address['taxIdType'] ?? null,
            'taxIdValue' => $address['taxIdValue'] ?? null,
            'accountOwner' => $address['accountOwner'] ?? null,
        ];
    }

    /**
     * Parse SOAP response into ShipmentResponse DTO.
     */
    protected function parseResponse(mixed $response): ShipmentResponse
    {
        // Extract parcel number and label from response
        $orderResult = $response->orderResult;
        $shipmentResult = $orderResult->shipmentResponses->parcelInformation ?? null;

        if (! $shipmentResult) {
            throw new \RuntimeException('Invalid shipment response from DPD');
        }

        $parcelNumber = $shipmentResult->parcelLabelNumber ?? '';
        $labelContent = $orderResult->output->content ?? '';
        $labelFormat = $orderResult->output->format ?? '';

        $label = new Label(
            content: $labelContent,
            format: $labelFormat,
        );

        return new ShipmentResponse(
            parcelNumber: $parcelNumber,
            label: $label,
            trackingUrl: "https://tracking.dpd.de/status/de_DE/parcel/{$parcelNumber}",
            rawResponse: (array) $response
        );
    }

    /**
     * Get default print options from config.
     */
    protected function getDefaultPrintOptions(): array
    {
        $printOption = $this->config['defaults']['print_options'] ?? [
            'outputFormat' => 'PDF',
            'paperFormat' => 'A4',
        ];

        // Wrap in printOptions structure
        return [
            'printOption' => [$printOption],
        ];
    }
}
