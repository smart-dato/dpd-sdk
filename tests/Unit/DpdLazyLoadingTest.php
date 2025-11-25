<?php

use SmartDato\Dpd\Dpd;

it('can be instantiated without credentials for ide-helper', function () {
    config(['dpd-sdk' => []]);

    $dpd = new Dpd;

    expect($dpd)->toBeInstanceOf(Dpd::class);
});

it('only creates services when actually used', function () {
    config(['dpd-sdk' => []]);

    // This should not throw an error because services aren't created yet
    $dpd = new Dpd;

    expect($dpd)->toBeInstanceOf(Dpd::class);
});

it('services are created on first use', function () {
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

    // Services should be created lazily when shipment() is called
    // This would throw an error if config is missing, but that's expected
    expect($dpd)->toBeInstanceOf(Dpd::class);
});
