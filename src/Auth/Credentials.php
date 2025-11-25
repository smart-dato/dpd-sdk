<?php

namespace SmartDato\Dpd\Auth;

readonly class Credentials
{
    public function __construct(
        public string $delisId,
        public string $password,
    ) {}
}
