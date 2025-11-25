<?php

namespace SmartDato\Dpd\DTOs;

readonly class Address
{
    public function __construct(
        public string $name,
        public string $street,
        public string $houseNumber,
        public string $zipCode,
        public string $city,
        public string $country,
        public ?string $company = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $comment = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'company' => $this->company,
            'street' => $this->street,
            'houseNumber' => $this->houseNumber,
            'zipCode' => $this->zipCode,
            'city' => $this->city,
            'country' => $this->country,
            'email' => $this->email,
            'phone' => $this->phone,
            'comment' => $this->comment,
        ], fn ($value) => $value !== null);
    }
}
