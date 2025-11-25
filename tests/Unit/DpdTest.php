<?php

use SmartDato\Dpd\Builders\ShipmentBuilder;
use SmartDato\Dpd\Dpd;
use SmartDato\Dpd\Services\ShipmentService;
use SmartDato\Dpd\Services\TrackingService;

beforeEach(function () {
    $this->config = [
        'environment' => 'staging',
        'credentials' => [
            'delis_id' => 'test_id',
            'password' => 'test_pass',
        ],
        'endpoints' => [
            'staging' => [
                'login' => 'https://test-login.dpd.com',
                'shipment' => 'https://test-shipment.dpd.com',
                'parcel_lifecycle' => 'https://test-parcel.dpd.com',
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
    ];
});

it('can be instantiated with config', function () {
    $mockShipmentService = Mockery::mock(ShipmentService::class);
    $mockTrackingService = Mockery::mock(TrackingService::class);

    $dpd = new Dpd($this->config, $mockShipmentService, $mockTrackingService);

    expect($dpd)->toBeInstanceOf(Dpd::class);
});

it('merges runtime config with default config', function () {
    config(['dpd-sdk' => [
        'environment' => 'production',
        'credentials' => [
            'delis_id' => 'default_id',
            'password' => 'default_pass',
        ],
    ]]);

    $mockShipmentService = Mockery::mock(ShipmentService::class);
    $mockTrackingService = Mockery::mock(TrackingService::class);

    $dpd = new Dpd([
        'credentials' => [
            'delis_id' => 'runtime_id',
        ],
    ], $mockShipmentService, $mockTrackingService);

    $config = $dpd->getConfig();

    expect($config['credentials']['delis_id'])->toBe('runtime_id')
        ->and($config['credentials']['password'])->toBe('default_pass')
        ->and($config['environment'])->toBe('production');
});

it('can create a shipment builder', function () {
    $mockShipmentService = Mockery::mock(ShipmentService::class);
    $mockTrackingService = Mockery::mock(TrackingService::class);

    $dpd = new Dpd($this->config, $mockShipmentService, $mockTrackingService);
    $builder = $dpd->shipment();

    expect($builder)->toBeInstanceOf(ShipmentBuilder::class);
});

it('accepts injected services for testing', function () {
    $mockShipmentService = Mockery::mock(ShipmentService::class);
    $mockTrackingService = Mockery::mock(TrackingService::class);

    $dpd = new Dpd($this->config, $mockShipmentService, $mockTrackingService);

    expect($dpd)->toBeInstanceOf(Dpd::class);
});

it('returns config via getConfig method', function () {
    $mockShipmentService = Mockery::mock(ShipmentService::class);
    $mockTrackingService = Mockery::mock(TrackingService::class);

    $dpd = new Dpd($this->config, $mockShipmentService, $mockTrackingService);
    $config = $dpd->getConfig();

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('environment')
        ->and($config)->toHaveKey('credentials');
});

it('handles empty config gracefully', function () {
    $mockShipmentService = Mockery::mock(ShipmentService::class);
    $mockTrackingService = Mockery::mock(TrackingService::class);

    $dpd = new Dpd([], $mockShipmentService, $mockTrackingService);

    expect($dpd)->toBeInstanceOf(Dpd::class);
});

it('runtime config takes priority over default config', function () {
    config(['dpd-sdk' => [
        'environment' => 'staging',
        'credentials' => [
            'delis_id' => 'default_id',
            'password' => 'default_pass',
        ],
    ]]);

    $mockShipmentService = Mockery::mock(ShipmentService::class);
    $mockTrackingService = Mockery::mock(TrackingService::class);

    $dpd = new Dpd([
        'environment' => 'production',
    ], $mockShipmentService, $mockTrackingService);

    $config = $dpd->getConfig();

    expect($config['environment'])->toBe('production')
        ->and($config['credentials']['delis_id'])->toBe('default_id');
});
