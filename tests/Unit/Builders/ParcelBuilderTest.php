<?php

use SmartDato\Dpd\Builders\ParcelBuilder;
use SmartDato\Dpd\DTOs\Parcel;

it('can build a parcel with all fields', function () {
    $builder = new ParcelBuilder;

    $builder->weight(2.5)
        ->content('Books')
        ->reference('ORDER-12345')
        ->dimensions(30.0, 20.0, 10.0);

    $data = $builder->toArray();

    expect($data)->toBeArray()
        ->and($data['weight'])->toBe(2.5)
        ->and($data['content'])->toBe('Books')
        ->and($data['reference'])->toBe('ORDER-12345')
        ->and($data['length'])->toBe(30.0)
        ->and($data['width'])->toBe(20.0)
        ->and($data['height'])->toBe(10.0);
});

it('can build a parcel with only required fields', function () {
    $builder = new ParcelBuilder;

    $builder->weight(1.5)
        ->content('Electronics');

    $data = $builder->toArray();

    expect($data)->toBeArray()
        ->and($data['weight'])->toBe(1.5)
        ->and($data['content'])->toBe('Electronics')
        ->and($data)->not->toHaveKey('reference');
});

it('can convert to Parcel DTO', function () {
    $builder = new ParcelBuilder;

    $builder->weight(2.5)
        ->content('Books')
        ->reference('ORDER-12345')
        ->dimensions(30.0, 20.0, 10.0);

    $dto = $builder->toDTO();

    expect($dto)->toBeInstanceOf(Parcel::class)
        ->and($dto->weight)->toBe(2.5)
        ->and($dto->content)->toBe('Books')
        ->and($dto->reference)->toBe('ORDER-12345')
        ->and($dto->length)->toBe(30.0)
        ->and($dto->width)->toBe(20.0)
        ->and($dto->height)->toBe(10.0);
});

it('supports method chaining', function () {
    $builder = new ParcelBuilder;

    $result = $builder->weight(1.0)
        ->content('Test');

    expect($result)->toBe($builder);
});

it('handles nullable dimensions', function () {
    $builder = new ParcelBuilder;

    $builder->weight(1.0)
        ->content('Small item')
        ->dimensions(null, null, null);

    $data = $builder->toArray();

    expect($data['length'])->toBeNull()
        ->and($data['width'])->toBeNull()
        ->and($data['height'])->toBeNull();
});
