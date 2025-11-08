<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paychangu Environment
    |--------------------------------------------------------------------------
    |
    | This value determines which Paychangu environment your application
    | is currently running in. This may determine how you prefer to
    | configure various services the application uses. Set this in your
    | ".env" file. Options: 'test', 'live'
    |
    */

    'environment' => env('PAYCHANGU_ENVIRONMENT') ?? 'test',

    /*
    |--------------------------------------------------------------------------
    | Paychangu Secret Key (Live)
    |--------------------------------------------------------------------------
    |
    | Your Paychangu secret key for live/production environment.
    | This should start with 'sk_live_'
    |
    */

    'secret_key' => env('PAYCHANGU_SECRET_KEY') ?? '',

    /*
    |--------------------------------------------------------------------------
    | Paychangu Test Key
    |--------------------------------------------------------------------------
    |
    | Your Paychangu test key for development/testing environment.
    | This should start with 'pk_test_' or 'sk_test_'
    |
    */

    'test_key' => env('PAYCHANGU_TEST_KEY') ?? '',

    /*
    |--------------------------------------------------------------------------
    | Paychangu Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for Paychangu API endpoints.
    | Default: https://api.paychangu.com
    |
    */

    'base_url' => env('PAYCHANGU_BASE_URL') ?? 'https://api.paychangu.com',

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for transactions.
    | Supported: MWK, USD
    |
    */

    'currency' => env('PAYCHANGU_CURRENCY') ?? 'MWK',

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for API responses.
    | Default: 30 seconds
    |
    */

    'timeout' => (int) (env('PAYCHANGU_TIMEOUT') ?? 30),
];
