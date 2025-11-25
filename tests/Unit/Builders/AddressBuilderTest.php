<?php

use SmartDato\Dpd\Builders\AddressBuilder;
use SmartDato\Dpd\DTOs\Address;

it('can build an address with all fields', function () {
    $builder = new AddressBuilder;

    $builder->name('John Doe')
        ->company('Acme Corp')
        ->street('Main Street')
        ->houseNumber('123')
        ->zipCode('12345')
        ->city('Berlin')
        ->country('DE')
        ->email('john@example.com')
        ->phone('+49123456789')
        ->comment('Ring doorbell');

    $data = $builder->toArray();

    expect($data)->toBeArray()
        ->and($data['name'])->toBe('John Doe')
        ->and($data['company'])->toBe('Acme Corp')
        ->and($data['street'])->toBe('Main Street')
        ->and($data['houseNumber'])->toBe('123')
        ->and($data['zipCode'])->toBe('12345')
        ->and($data['city'])->toBe('Berlin')
        ->and($data['country'])->toBe('DE')
        ->and($data['email'])->toBe('john@example.com')
        ->and($data['phone'])->toBe('+49123456789')
        ->and($data['comment'])->toBe('Ring doorbell');
});

it('can build an address with only required fields', function () {
    $builder = new AddressBuilder;

    $builder->name('Jane Smith')
        ->street('Second Avenue')
        ->houseNumber('456')
        ->zipCode('54321')
        ->city('Hamburg')
        ->country('DE');

    $data = $builder->toArray();

    expect($data)->toBeArray()
        ->and($data['name'])->toBe('Jane Smith')
        ->and($data['street'])->toBe('Second Avenue')
        ->and($data['houseNumber'])->toBe('456')
        ->and($data['zipCode'])->toBe('54321')
        ->and($data['city'])->toBe('Hamburg')
        ->and($data['country'])->toBe('DE')
        ->and($data)->not->toHaveKey('company')
        ->and($data)->not->toHaveKey('email')
        ->and($data)->not->toHaveKey('phone');
});

it('can convert to Address DTO', function () {
    $builder = new AddressBuilder;

    $builder->name('John Doe')
        ->company('Acme Corp')
        ->street('Main Street')
        ->houseNumber('123')
        ->zipCode('12345')
        ->city('Berlin')
        ->country('DE')
        ->email('john@example.com')
        ->phone('+49123456789')
        ->comment('Ring doorbell');

    $dto = $builder->toDTO();

    expect($dto)->toBeInstanceOf(Address::class)
        ->and($dto->name)->toBe('John Doe')
        ->and($dto->company)->toBe('Acme Corp')
        ->and($dto->street)->toBe('Main Street')
        ->and($dto->houseNumber)->toBe('123')
        ->and($dto->zipCode)->toBe('12345')
        ->and($dto->city)->toBe('Berlin')
        ->and($dto->country)->toBe('DE')
        ->and($dto->email)->toBe('john@example.com')
        ->and($dto->phone)->toBe('+49123456789')
        ->and($dto->comment)->toBe('Ring doorbell');
});

it('supports method chaining', function () {
    $builder = new AddressBuilder;

    $result = $builder->name('Test')
        ->street('Test St')
        ->houseNumber('1')
        ->zipCode('12345')
        ->city('Test City')
        ->country('DE');

    expect($result)->toBe($builder);
});
