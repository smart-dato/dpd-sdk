<?php

use SmartDato\Dpd\Builders\ShipmentBuilder;
use SmartDato\Dpd\Dpd;

it('creates a shipment builder instance', function () {
    config([
        'dpd-sdk' => [
            'environment' => 'staging',
            'credentials' => [
                'delis_id' => 'test_id',
                'password' => 'test_pass',
            ],
            'endpoints' => [
                'staging' => [
                    'login' => 'https://public-ws-stage.dpd.com/services/LoginService/V2_0?wsdl',
                    'shipment' => 'https://public-ws-stage.dpd.com/services/ShipmentService/V4_5?wsdl',
                    'parcel_lifecycle' => 'https://public-ws-stage.dpd.com/services/ParcelLifeCycleService/V2_0?wsdl',
                ],
            ],
            'cache' => [
                'store' => null,
                'prefix' => 'dpd_auth',
                'ttl' => 86400,
            ],
            'soap' => [
                'trace' => true,
                'exceptions' => true,
            ],
        ],
    ]);

    $dpd = new Dpd;

    $builder = $dpd->shipment();

    expect($builder)->toBeInstanceOf(ShipmentBuilder::class);
});

it('can build complete shipment data structure', function () {
    config([
        'dpd-sdk' => [
            'environment' => 'staging',
            'credentials' => [
                'delis_id' => 'test_id',
                'password' => 'test_pass',
            ],
            'endpoints' => [
                'staging' => [
                    'login' => 'https://public-ws-stage.dpd.com/services/LoginService/V2_0?wsdl',
                    'shipment' => 'https://public-ws-stage.dpd.com/services/ShipmentService/V4_5?wsdl',
                    'parcel_lifecycle' => 'https://public-ws-stage.dpd.com/services/ParcelLifeCycleService/V2_0?wsdl',
                ],
            ],
            'cache' => [
                'store' => null,
                'prefix' => 'dpd_auth',
                'ttl' => 86400,
            ],
            'soap' => [
                'trace' => true,
                'exceptions' => true,
            ],
        ],
    ]);

    $dpd = new Dpd;

    $builder = $dpd->shipment()
        ->sendingDepot('0180')
        ->sender(fn ($sender) => $sender
            ->name('John Doe')
            ->company('Acme Corp')
            ->street('Main Street')
            ->houseNumber('123')
            ->zipCode('12345')
            ->city('Berlin')
            ->country('DE')
        )
        ->recipient(fn ($recipient) => $recipient
            ->name('Jane Smith')
            ->street('Second Avenue')
            ->houseNumber('456')
            ->zipCode('54321')
            ->city('Hamburg')
            ->country('DE')
            ->email('jane@example.com')
            ->phone('+49123456789')
        )
        ->parcel(fn ($parcel) => $parcel
            ->weight(2.5)
            ->content('Books')
            ->reference('ORDER-12345')
        )
        ->labelFormat('PDF')
        ->paperFormat('A4');

    $data = $builder->toArray();

    expect($data)->toBeArray()
        ->and($data)->toHaveKey('sender')
        ->and($data)->toHaveKey('recipient')
        ->and($data)->toHaveKey('parcels')
        ->and($data['sender']['name'])->toBe('John Doe')
        ->and($data['recipient']['name'])->toBe('Jane Smith')
        ->and($data['parcels'][0]['weight'])->toBe(2.5)
        ->and($data['sending_depot'])->toBe('0180');
});
