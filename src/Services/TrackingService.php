<?php

namespace SmartDato\Dpd\Services;

use DateTimeImmutable;
use Illuminate\Support\Collection;
use SmartDato\Dpd\Clients\ParcelLifeCycleServiceClient;
use SmartDato\Dpd\DTOs\TrackingEvent;

class TrackingService
{
    public function __construct(
        protected ParcelLifeCycleServiceClient $client
    ) {}

    /**
     * Track a parcel by its number.
     *
     * @param  string  $parcelNumber  The parcel number to track
     * @return Collection<TrackingEvent>
     */
    public function track(string $parcelNumber): Collection
    {
        $response = $this->client->getTrackingData($parcelNumber);

        return $this->parseTrackingResponse($response);
    }

    /**
     * Parse SOAP response into collection of TrackingEvent DTOs.
     */
    protected function parseTrackingResponse(mixed $response): Collection
    {
        $events = [];

        // Parse the tracking events from the response
        if (isset($response->trackingData->scanInfo)) {
            $scanInfos = is_array($response->trackingData->scanInfo)
                ? $response->trackingData->scanInfo
                : [$response->trackingData->scanInfo];

            foreach ($scanInfos as $scanInfo) {
                $events[] = new TrackingEvent(
                    status: $scanInfo->scan ?? 'unknown',
                    timestamp: new DateTimeImmutable($scanInfo->date ?? 'now'),
                    location: $scanInfo->depot ?? 'unknown',
                    description: $scanInfo->scanText ?? null
                );
            }
        }

        return collect($events);
    }
}
