<?php

namespace SmartDato\Dpd;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DpdServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('dpd-sdk')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        // Bind default Dpd instance (uses config from .env)
        // This is used by the Facade when no runtime config is provided
        $this->app->singleton(Dpd::class, fn ($app) => new Dpd);
    }
}
