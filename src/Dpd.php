<?php

namespace SmartDato\Dpd;

use Illuminate\Support\Collection;
use SmartDato\Dpd\Auth\Credentials;
use SmartDato\Dpd\Auth\TokenManager;
use SmartDato\Dpd\Builders\ShipmentBuilder;
use SmartDato\Dpd\Clients\LoginServiceClient;
use SmartDato\Dpd\Clients\ParcelLifeCycleServiceClient;
use SmartDato\Dpd\Clients\ShipmentServiceClient;
use SmartDato\Dpd\Services\ShipmentService;
use SmartDato\Dpd\Services\TrackingService;

class Dpd
{
    protected array $config;

    protected ShipmentService $shipmentService;

    protected TrackingService $trackingService;

    /**
     * Create a new Dpd instance.
     *
     * @param  array|null  $config  Runtime configuration that overrides default config
     * @param  ShipmentService|null  $shipmentService  For dependency injection (testing)
     * @param  TrackingService|null  $trackingService  For dependency injection (testing)
     */
    public function __construct(
        ?array $config = null,
        ?ShipmentService $shipmentService = null,
        ?TrackingService $trackingService = null
    ) {
        // Merge: default config + runtime config (runtime takes priority)
        $this->config = array_replace_recursive(
            config('dpd-sdk', []),
            $config ?? []
        );

        // Create services with merged config (or use injected for testing)
        $this->shipmentService = $shipmentService ?? $this->createShipmentService();
        $this->trackingService = $trackingService ?? $this->createTrackingService();
    }

    /**
     * Start building a new shipment.
     */
    public function shipment(): ShipmentBuilder
    {
        return new ShipmentBuilder($this->shipmentService, $this->config);
    }

    /**
     * Track a parcel by parcel number.
     */
    public function track(string $parcelNumber): Collection
    {
        return $this->trackingService->track($parcelNumber);
    }

    /**
     * Create all dependencies using the merged configuration.
     */
    protected function createShipmentService(): ShipmentService
    {
        $credentials = new Credentials(
            $this->config['credentials']['delis_id'],
            $this->config['credentials']['password']
        );

        $loginClient = new LoginServiceClient(
            $this->getEndpoint('login'),
            $this->config['soap']
        );

        $tokenManager = new TokenManager(
            app('cache')->store($this->config['cache']['store'] ?? null),
            $loginClient,
            $credentials,
            $this->config['cache']
        );

        $shipmentClient = new ShipmentServiceClient(
            $this->getEndpoint('shipment'),
            $this->config['soap'],
            $tokenManager
        );

        return new ShipmentService($shipmentClient, $this->config);
    }

    /**
     * Create tracking service with merged configuration.
     */
    protected function createTrackingService(): TrackingService
    {
        $credentials = new Credentials(
            $this->config['credentials']['delis_id'],
            $this->config['credentials']['password']
        );

        $loginClient = new LoginServiceClient(
            $this->getEndpoint('login'),
            $this->config['soap']
        );

        $tokenManager = new TokenManager(
            app('cache')->store($this->config['cache']['store'] ?? null),
            $loginClient,
            $credentials,
            $this->config['cache']
        );

        $trackingClient = new ParcelLifeCycleServiceClient(
            $this->getEndpoint('parcel_lifecycle'),
            $this->config['soap'],
            $tokenManager
        );

        return new TrackingService($trackingClient);
    }

    /**
     * Get the endpoint URL for a specific service.
     */
    protected function getEndpoint(string $service): string
    {
        $env = $this->config['environment'];

        return $this->config['endpoints'][$env][$service];
    }

    /**
     * Get the current configuration.
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
