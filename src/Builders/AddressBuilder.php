<?php

namespace SmartDato\Dpd\Builders;

use SmartDato\Dpd\DTOs\Address;

class AddressBuilder
{
    protected array $data = [];

    public function name(string $name): self
    {
        $this->data['name'] = $name;

        return $this;
    }

    public function company(?string $company): self
    {
        $this->data['company'] = $company;

        return $this;
    }

    public function street(string $street): self
    {
        $this->data['street'] = $street;

        return $this;
    }

    public function houseNumber(string $houseNumber): self
    {
        $this->data['houseNumber'] = $houseNumber;

        return $this;
    }

    public function zipCode(string $zipCode): self
    {
        $this->data['zipCode'] = $zipCode;

        return $this;
    }

    public function city(string $city): self
    {
        $this->data['city'] = $city;

        return $this;
    }

    public function country(string $country): self
    {
        $this->data['country'] = $country;

        return $this;
    }

    public function email(?string $email): self
    {
        $this->data['email'] = $email;

        return $this;
    }

    public function phone(?string $phone): self
    {
        $this->data['phone'] = $phone;

        return $this;
    }

    public function comment(?string $comment): self
    {
        $this->data['comment'] = $comment;

        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toDTO(): Address
    {
        return new Address(
            name: $this->data['name'],
            street: $this->data['street'],
            houseNumber: $this->data['houseNumber'],
            zipCode: $this->data['zipCode'],
            city: $this->data['city'],
            country: $this->data['country'],
            company: $this->data['company'] ?? null,
            email: $this->data['email'] ?? null,
            phone: $this->data['phone'] ?? null,
            comment: $this->data['comment'] ?? null,
        );
    }
}
