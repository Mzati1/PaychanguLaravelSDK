<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paychangu API Keys
    |--------------------------------------------------------------------------
    |
    | Your Paychangu API credentials. Get these from your Paychangu dashboard.
    | Sign up at: https://paychangu.com
    |
    */
    'secret_key' => env('PAYCHANGU_SECRET_KEY', ''),
    'test_key' => env('PAYCHANGU_TEST_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Set to 'test' for testing or 'live' for production
    | Options: 'test', 'live'
    |
    */
    'environment' => env('PAYCHANGU_ENVIRONMENT', 'live'),

    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    |
    */
    'base_url' => env('PAYCHANGU_BASE_URL', 'https://api.paychangu.com'),
    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | Default currency for transactions.
    | Supported: MWK (Malawian Kwacha), USD, ZAR, etc.
    |
    */
    'currency' => env('PAYCHANGU_CURRENCY', 'MWK'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time (in seconds) to wait for API responses
    |
    */
    'timeout' => env('PAYCHANGU_TIMEOUT', 30),
];
