<?php

namespace SmartDato\Dpd\DTOs;

readonly class Parcel
{
    public function __construct(
        public float $weight,
        public string $content,
        public ?string $reference = null,
        public ?float $length = null,
        public ?float $width = null,
        public ?float $height = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'weight' => $this->weight,
            'content' => $this->content,
            'reference' => $this->reference,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
        ], fn ($value) => $value !== null);
    }
}
