<?php

namespace SmartDato\Dpd\Builders;

use SmartDato\Dpd\DTOs\ShipmentResponse;
use SmartDato\Dpd\Services\ShipmentService;

class ShipmentBuilder
{
    protected array $data = [];

    public function __construct(
        protected ShipmentService $service,
        protected array $config = []
    ) {}

    /**
     * Set the sender address using a closure.
     */
    public function sender(callable $callback): self
    {
        $builder = new AddressBuilder;
        $callback($builder);
        $this->data['sender'] = $builder->toArray();

        return $this;
    }

    /**
     * Set the recipient address using a closure.
     */
    public function recipient(callable $callback): self
    {
        $builder = new AddressBuilder;
        $callback($builder);
        $this->data['recipient'] = $builder->toArray();

        return $this;
    }

    /**
     * Add a parcel using a closure.
     */
    public function parcel(callable $callback): self
    {
        $builder = new ParcelBuilder;
        $callback($builder);
        $this->data['parcels'][] = $builder->toArray();

        return $this;
    }

    /**
     * Set the label format (PDF or ZPL).
     */
    public function labelFormat(string $format): self
    {
        if (! isset($this->data['print_options']['printOption'])) {
            $this->data['print_options']['printOption'] = [[]];
        }

        $this->data['print_options']['printOption'][0]['outputFormat'] = $format;

        return $this;
    }

    /**
     * Set the paper format (A4 or A7).
     */
    public function paperFormat(string $format): self
    {
        if (! isset($this->data['print_options']['printOption'])) {
            $this->data['print_options']['printOption'] = [[]];
        }

        $this->data['print_options']['printOption'][0]['paperFormat'] = $format;

        return $this;
    }

    /**
     * Set the sending depot (defaults to your delis_id).
     */
    public function sendingDepot(string $depot): self
    {
        $this->data['sending_depot'] = $depot;

        return $this;
    }

    /**
     * Set the DPD product type (e.g., 'CL' for Classic).
     */
    public function product(string $product): self
    {
        $this->data['product'] = $product;

        return $this;
    }

    /**
     * Set customer reference number 1 (e.g., order number, customer ID).
     */
    public function customerReferenceNumber1(string $reference): self
    {
        $this->data['mpsCustomerReferenceNumber1'] = $reference;

        return $this;
    }

    /**
     * Set customer reference number 2.
     */
    public function customerReferenceNumber2(string $reference): self
    {
        $this->data['mpsCustomerReferenceNumber2'] = $reference;

        return $this;
    }

    /**
     * Set customer reference number 3.
     */
    public function customerReferenceNumber3(string $reference): self
    {
        $this->data['mpsCustomerReferenceNumber3'] = $reference;

        return $this;
    }

    /**
     * Set customer reference number 4.
     */
    public function customerReferenceNumber4(string $reference): self
    {
        $this->data['mpsCustomerReferenceNumber4'] = $reference;

        return $this;
    }

    /**
     * Create the shipment and get the response.
     *
     * @throws \Throwable
     */
    public function create(): ShipmentResponse
    {
        return $this->service->createShipment($this->data);
    }

    /**
     * Get the raw shipment data (useful for debugging).
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
