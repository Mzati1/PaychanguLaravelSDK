<?php

namespace Mzati\PaychanguSDK\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mzati\PaychanguSDK\PaychanguService
 *
 * @method static object initiateTransaction(array $data)
 * @method static object verifyTransaction(string $txRef)
 * @method static string|null getTransactionReference(\Illuminate\Http\Request $request)
 * @method static string getEnvironment()
 * @method static bool isTest()
 * @method static bool isLive()
 */
class Paychangu extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'paychangu';
    }
}
