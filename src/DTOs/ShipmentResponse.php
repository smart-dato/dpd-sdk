<?php

namespace SmartDato\Dpd\DTOs;

readonly class ShipmentResponse
{
    public function __construct(
        public string $parcelNumber,
        public Label $label,
        public ?string $mpsId = null,
        public ?string $trackingUrl = null,
        public ?array $rawResponse = null,
    ) {}
}
