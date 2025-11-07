<?php

namespace Mzati\PaychanguSDK\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mzati\PaychanguSDK\Paychangu
 */
class Paychangu extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'paychangu';
    }
}
