<?php

namespace SmartDato\Dpd\DTOs;

readonly class Label
{
    public function __construct(
        public string $content,
        public string $format,
        public ?string $barcode = null,
        public ?string $mimeType = null,
    ) {}
}
