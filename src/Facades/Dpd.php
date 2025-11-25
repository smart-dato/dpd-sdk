<?php

namespace SmartDato\Dpd\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SmartDato\Dpd\Dpd
 */
class Dpd extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SmartDato\Dpd\Dpd::class;
    }
}
