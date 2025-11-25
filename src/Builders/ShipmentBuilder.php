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
        $this->data['print_options']['printerLanguage'] = $format;

        return $this;
    }

    /**
     * Set the paper format (A4 or A7).
     */
    public function paperFormat(string $format): self
    {
        $this->data['print_options']['paperFormat'] = $format;

        return $this;
    }

    /**
     * Create the shipment and get the response.
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
