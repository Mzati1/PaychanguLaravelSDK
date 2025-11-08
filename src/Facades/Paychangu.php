<?php

namespace Mzati\PaychanguSDK\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Mzati\PaychanguSDK\Endpoints\PaymentEndpoint payment()
 * @method static \Mzati\PaychanguSDK\Endpoints\MobileMoneyEndpoint mobile_money()
 * @method static \Mzati\PaychanguSDK\Endpoints\BankTransferEndpoint bank_transfer()
 * @method static \Mzati\PaychanguSDK\Endpoints\DirectCardEndpoint direct_card()
 * @method static string getTransactionReference(\Illuminate\Http\Request $request)
 * @method static string getEnvironment()
 * @method static bool isTest()
 * @method static bool isLive()
 */
class Paychangu extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'paychangu';
    }
}
