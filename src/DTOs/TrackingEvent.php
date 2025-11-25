<?php

namespace SmartDato\Dpd\DTOs;

use DateTimeImmutable;

readonly class TrackingEvent
{
    public function __construct(
        public string $status,
        public DateTimeImmutable $timestamp,
        public string $location,
        public ?string $description = null,
    ) {}
}
