<?php

namespace SmartDato\Dpd\Builders;

use SmartDato\Dpd\DTOs\Parcel;

class ParcelBuilder
{
    protected array $data = [];

    public function weight(float $weight): self
    {
        $this->data['weight'] = $weight;

        return $this;
    }

    public function content(string $content): self
    {
        $this->data['content'] = $content;

        return $this;
    }

    public function reference(?string $reference): self
    {
        $this->data['reference'] = $reference;

        return $this;
    }

    public function dimensions(?float $length, ?float $width, ?float $height): self
    {
        $this->data['length'] = $length;
        $this->data['width'] = $width;
        $this->data['height'] = $height;

        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toDTO(): Parcel
    {
        return new Parcel(
            weight: $this->data['weight'],
            content: $this->data['content'],
            reference: $this->data['reference'] ?? null,
            length: $this->data['length'] ?? null,
            width: $this->data['width'] ?? null,
            height: $this->data['height'] ?? null,
        );
    }
}
