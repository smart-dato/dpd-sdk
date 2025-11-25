<?php

use SmartDato\Dpd\Builders\ShipmentBuilder;
use SmartDato\Dpd\DTOs\ShipmentResponse;
use SmartDato\Dpd\Services\ShipmentService;

beforeEach(function () {
    $this->mockService = Mockery::mock(ShipmentService::class);
});

afterEach(function () {
    Mockery::close();
});

it('can build a shipment with sender and recipient', function () {
    $builder = new ShipmentBuilder($this->mockService);

    $builder->sender(fn ($sender) => $sender
        ->name('John Doe')
        ->street('Main St')
        ->houseNumber('123')
        ->zipCode('12345')
        ->city('Berlin')
        ->country('DE')
    )->recipient(fn ($recipient) => $recipient
        ->name('Jane Smith')
        ->street('Second Ave')
        ->houseNumber('456')
        ->zipCode('54321')
        ->city('Hamburg')
        ->country('DE')
    );

    $data = $builder->toArray();

    expect($data)->toBeArray()
        ->and($data)->toHaveKey('sender')
        ->and($data)->toHaveKey('recipient')
        ->and($data['sender']['name'])->toBe('John Doe')
        ->and($data['recipient']['name'])->toBe('Jane Smith');
});

it('can add multiple parcels', function () {
    $builder = new ShipmentBuilder($this->mockService);

    $builder->parcel(fn ($parcel) => $parcel
        ->weight(2.5)
        ->content('Books')
    )->parcel(fn ($parcel) => $parcel
        ->weight(1.0)
        ->content('Electronics')
    );

    $data = $builder->toArray();

    expect($data['parcels'])->toBeArray()
        ->and($data['parcels'])->toHaveCount(2)
        ->and($data['parcels'][0]['weight'])->toBe(2.5)
        ->and($data['parcels'][1]['weight'])->toBe(1.0);
});

it('can set label format', function () {
    $builder = new ShipmentBuilder($this->mockService);

    $builder->labelFormat('ZPL');

    $data = $builder->toArray();

    expect($data['print_options']['printOption'][0]['outputFormat'])->toBe('ZPL');
});

it('can set paper format', function () {
    $builder = new ShipmentBuilder($this->mockService);

    $builder->paperFormat('A7');

    $data = $builder->toArray();

    expect($data['print_options']['printOption'][0]['paperFormat'])->toBe('A7');
});

it('can set sending depot', function () {
    $builder = new ShipmentBuilder($this->mockService);

    $builder->sendingDepot('0180');

    $data = $builder->toArray();

    expect($data['sending_depot'])->toBe('0180');
});

it('can set product', function () {
    $builder = new ShipmentBuilder($this->mockService);

    $builder->product('CL');

    $data = $builder->toArray();

    expect($data['product'])->toBe('CL');
});

it('can create a shipment', function () {
    $mockResponse = new ShipmentResponse(
        parcelNumber: '123456789',
        label: new \SmartDato\Dpd\DTOs\Label('base64content', 'PDF'),
        trackingUrl: 'https://tracking.dpd.de/status/de_DE/parcel/123456789'
    );

    $this->mockService->shouldReceive('createShipment')
        ->once()
        ->andReturn($mockResponse);

    $builder = new ShipmentBuilder($this->mockService);

    $builder->sender(fn ($sender) => $sender
        ->name('John Doe')
        ->street('Main St')
        ->houseNumber('123')
        ->zipCode('12345')
        ->city('Berlin')
        ->country('DE')
    );

    $response = $builder->create();

    expect($response)->toBeInstanceOf(ShipmentResponse::class)
        ->and($response->parcelNumber)->toBe('123456789');
});

it('supports method chaining', function () {
    $builder = new ShipmentBuilder($this->mockService);

    $result = $builder->sendingDepot('0180')
        ->product('CL')
        ->labelFormat('PDF')
        ->paperFormat('A4');

    expect($result)->toBe($builder);
});
