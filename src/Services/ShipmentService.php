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
            'generalShipmentData' => [
                'sender' => $shipmentData['sender'],
                'recipient' => $shipmentData['recipient'],
            ],
        ];

        // Add parcels
        if (isset($shipmentData['parcels'])) {
            $order['parcels'] = $shipmentData['parcels'];
        }

        // Add product and service data if provided
        if (isset($shipmentData['product_and_service_data'])) {
            $order['productAndServiceData'] = $shipmentData['product_and_service_data'];
        }

        return $order;
    }

    /**
     * Parse SOAP response into ShipmentResponse DTO.
     */
    protected function parseResponse(mixed $response): ShipmentResponse
    {
        // Extract parcel number and label from response
        $shipmentResult = $response->orderResult->shipmentResponses->parcelInformation ?? null;

        if (! $shipmentResult) {
            throw new \RuntimeException('Invalid shipment response from DPD');
        }

        $parcelNumber = $shipmentResult->parcelLabelNumber ?? '';
        $labelContent = $response->orderResult->parcellabelsPDF ?? '';
        $labelFormat = $this->config['defaults']['label_format'] ?? 'PDF';

        $label = new Label(
            content: base64_decode($labelContent),
            format: $labelFormat,
            mimeType: $labelFormat === 'PDF' ? 'application/pdf' : 'text/plain'
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
        return $this->config['defaults']['print_options'] ?? [
            'printerLanguage' => 'PDF',
            'paperFormat' => 'A4',
        ];
    }
}
