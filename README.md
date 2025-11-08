# Paychangu Laravel SDK

A Laravel package for integrating Paychangu payment gateway into your Laravel applications.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mzati/paychangusdk.svg?style=flat-square)](https://packagist.org/packages/mzati/paychangusdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/Mzati1/PaychanguLaravelSDK/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Mzati1/PaychanguLaravelSDK/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mzati/paychangusdk.svg?style=flat-square)](https://packagist.org/packages/mzati/paychangusdk)

## Requirements

- PHP 8.1 or higher
- Laravel 9.0, 10.0, 11.0, or 12.0

## Installation

Install the package via Composer:

```bash
composer require mzati/paychangusdk
```

## Setup

Run the setup command to configure the SDK:

```bash
php artisan paychangu:setup
```

This command will:
- Publish the configuration file to `config/paychanguConfig.php`
- Add necessary environment variables to your `.env` file

### Manual Configuration

Alternatively, you can manually configure the SDK by publishing the config file:

```bash
php artisan vendor:publish --tag="paychanguConfig-config"
```

Then add the following variables to your `.env` file:

```env
PAYCHANGU_SECRET_KEY=sk_live_xxxxx
PAYCHANGU_TEST_KEY=pk_test_xxxxx
PAYCHANGU_ENVIRONMENT=test
PAYCHANGU_BASE_URL=https://api.paychangu.com
PAYCHANGU_CURRENCY=MWK
PAYCHANGU_TIMEOUT=30
```

### Configuration Options

The package supports the following configuration options:

- **secret_key**: Your Paychangu live API key
- **test_key**: Your Paychangu test API key
- **environment**: Set to `test` for testing or `live` for production
- **base_url**: Paychangu API base URL (default: `https://api.paychangu.com`)
- **currency**: Default currency code (MWK, USD, ZAR, GBP, EUR)
- **timeout**: Request timeout in seconds (default: 30)

## Usage

### Initiating a Transaction

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

$transaction = Paychangu::initiateTransaction([
    'amount' => 1000,
    'email' => 'customer@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'tx_ref' => 'TXN-' . time(),
    'callback_url' => route('payment.callback'),
    'currency' => 'MWK', // Optional, uses config default if not provided
]);

// Redirect user to checkout
return redirect($transaction->data['checkout_url']);
```

### Verifying a Transaction

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

public function handleCallback(Request $request)
{
    $txRef = Paychangu::getTransactionReference($request);
    
    if (!$txRef) {
        return redirect()->route('payment.failed');
    }
    
    $verification = Paychangu::verifyTransaction($txRef);
    
    if ($verification->data['status'] === 'successful') {
        // Payment successful
        return redirect()->route('payment.success');
    }
    
    return redirect()->route('payment.failed');
}
```

## Supported Endpoints

### Payment Initiation

**Method**: `initiateTransaction(array $data)`

Initiates a new payment transaction and returns a checkout URL.

**Required Parameters**:
- `amount` (numeric): Transaction amount
- `email` (string): Customer email address
- `tx_ref` (string): Your unique transaction reference (min 3 characters)
- `callback_url` (string): URL to redirect after payment

**Optional Parameters**:
- `currency` (string): Currency code (defaults to config)
- `first_name` (string): Customer first name
- `last_name` (string): Customer last name
- `return_url` (string): Alternative return URL
- `customization` (array): Custom branding with `title`, `description`, `logo`
- `meta` (array): Additional metadata

**Returns**: Object containing `checkout_url` and transaction details

### Payment Verification

**Method**: `verifyTransaction(string $txRef)`

Verifies the status of a transaction using your transaction reference.

**Parameters**:
- `txRef` (string): Your transaction reference

**Returns**: Object containing transaction status and details

## Helper Methods

### Get Transaction Reference

```php
$txRef = Paychangu::getTransactionReference($request);
```

Extracts the transaction reference from a callback request.

### Environment Checks

```php
$environment = Paychangu::getEnvironment(); // 'test' or 'live'
$isTest = Paychangu::isTest(); // boolean
$isLive = Paychangu::isLive(); // boolean
```

## Artisan Commands

### Check Integration Status

```bash
php artisan paychangu:status
```

Displays current configuration and verifies the SDK setup.

### Setup Configuration

```bash
php artisan paychangu:setup
```

Interactive setup wizard for configuring the SDK.

Use `--force` flag to overwrite existing configuration:

```bash
php artisan paychangu:setup --force
```

## Exception Handling

The SDK throws `PaychanguException` for errors. Always wrap API calls in try-catch blocks:

```php
use Mzati\PaychanguSDK\Facades\Paychangu;
use Mzati\PaychanguSDK\Exceptions\PaychanguException;

try {
    $transaction = Paychangu::initiateTransaction($data);
} catch (PaychanguException $e) {
    // Handle error
    Log::error('Payment failed: ' . $e->getMessage());
    return back()->with('error', 'Payment initiation failed');
}
```

## Logging

The SDK automatically logs all transactions and errors to Laravel's default log channel. Log entries include:

- Transaction initiation attempts
- Verification requests
- API errors and responses
- Connection issues

## Supported Currencies

- MWK (Malawian Kwacha)
- USD (United States Dollar)

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

## Security

If you discover any security vulnerabilities, please email mzatitembo01@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/Mzati1/PaychanguLaravelSDK).

For Paychangu API documentation and support, visit [https://paychangu.com](https://paychangu.com).
