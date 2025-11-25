<?php

use SmartDato\Dpd\Dpd;
use SmartDato\Dpd\DpdServiceProvider;
use SmartDato\Dpd\Facades\Dpd as DpdFacade;

it('service provider is registered', function () {
    $providers = app()->getLoadedProviders();

    expect($providers)->toHaveKey(DpdServiceProvider::class);
});

it('publishes config file', function () {
    $configPath = config_path('dpd-sdk.php');

    expect($configPath)->toBeString();
});

it('loads package config', function () {
    expect(config('dpd-sdk'))->toBeArray();
});

it('facade accessor returns correct name', function () {
    $facade = new DpdFacade;
    $reflection = new ReflectionClass($facade);
    $method = $reflection->getMethod('getFacadeAccessor');
    $method->setAccessible(true);

    expect($method->invoke($facade))->toBe(Dpd::class);
});
