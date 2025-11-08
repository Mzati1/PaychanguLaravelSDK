# Paychangu Laravel SDK

A Laravel package for integrating Paychangu payment gateway into your Laravel applications.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mzati/paychangusdk.svg?style=flat-square)](https://packagist.org/packages/mzati/paychangusdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/Mzati1/PaychanguLaravelSDK/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Mzati1/PaychanguLaravelSDK/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mzati/paychangusdk.svg?style=flat-square)](https://packagist.org/packages/mzati/paychangusdk)

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or 12.0

## Installation

Install via Composer:

```bash
composer require mzati/paychangusdk
```

## Configuration

### Quick Setup

Run the interactive setup command:

```bash
php artisan paychangu:setup
```

This will guide you through configuring your environment variables and publish the configuration file.

### Publish Config files

```bash
php artisan vendor:publish --tag=paychangu-config
```

### Manual Setup

Add these variables to your `.env` file:

```env
PAYCHANGU_SECRET_KEY=sk_live_xxxxx
PAYCHANGU_TEST_KEY=pk_test_xxxxx
PAYCHANGU_ENVIRONMENT=test
PAYCHANGU_BASE_URL=https://api.paychangu.com
PAYCHANGU_CURRENCY=MWK
PAYCHANGU_TIMEOUT=30
```

**Configuration Options:**

- `PAYCHANGU_SECRET_KEY` - Your live API key
- `PAYCHANGU_TEST_KEY` - Your test API key
- `PAYCHANGU_ENVIRONMENT` - Set to `test` or `live`
- `PAYCHANGU_BASE_URL` - API base URL (default: https://api.paychangu.com)
- `PAYCHANGU_CURRENCY` - Default currency (MWK, USD, ZAR, GBP, EUR)
- `PAYCHANGU_TIMEOUT` - Request timeout in seconds (default: 30)

### Verify Configuration

Check your setup:

```bash
php artisan paychangu:status
```

## Usage

### Payment Endpoint

The payment endpoint handles standard checkout flows with hosted payment pages.

#### Initiate Payment

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

$transaction = Paychangu::payment()->initiate([
    'amount' => 1000,
    'email' => 'customer@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'tx_ref' => 'TXN-' . uniqid(),
    'callback_url' => route('payment.callback'),
    'currency' => 'MWK', // Optional
]);

// Redirect to checkout
return redirect($transaction->data['checkout_url']);
```

**Required Fields:**
- `amount` - Transaction amount
- `email` - Customer email
- `tx_ref` - Unique transaction reference (min 3 characters)
- `callback_url` - Return URL after payment

**Optional Fields:**
- `currency` - Defaults to config value
- `first_name` - Customer first name
- `last_name` - Customer last name
- `return_url` - Alternative return URL
- `customization` - Custom branding array
- `meta` - Additional metadata array

#### Verify Payment

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

public function handleCallback(Request $request)
{
    $txRef = Paychangu::getTransactionReference($request);
    
    $verification = Paychangu::payment()->verify($txRef);
    
    if ($verification->data['status'] === 'successful') {
        // Payment successful
        return redirect()->route('payment.success');
    }
    
    return redirect()->route('payment.failed');
}
```

### Mobile Money Endpoint

Direct mobile money payments via supported operators.

#### Get Available Operators

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

$operators = Paychangu::mobile_money()->getMobileMoneyOperators();

// Returns list of operators with reference IDs
```

#### Charge Mobile Money

```php
$charge = Paychangu::mobile_money()->chargeMobileMoney([
    'mobile_money_operator_ref_id' => 'operator_ref_id',
    'mobile' => '265991234567',
    'amount' => 1000,
    'charge_id' => 'CHARGE-' . uniqid(),
    'email' => 'customer@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
]);
```

**Required Fields:**
- `mobile_money_operator_ref_id` - Get from operators endpoint
- `mobile` - Customer phone number
- `amount` - Transaction amount
- `charge_id` - Unique charge identifier

**Optional Fields:**
- `email` - Customer email
- `first_name` - Customer first name
- `last_name` - Customer last name

#### Verify Mobile Money Charge

```php
$status = Paychangu::mobile_money()->verifyDirectChargeStatus($chargeId);

if ($status->data['status'] === 'successful') {
    // Payment successful
}
```

#### Get Charge Details

```php
$details = Paychangu::mobile_money()->singleMobileChargeDetails($chargeId);
```

### Bank Transfer Endpoint

Direct bank transfer payments.

#### Initialize Bank Transfer

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

$transfer = Paychangu::bank_transfer()->bankTransfer([
    'amount' => 5000,
    'charge_id' => 'BANK-' . uniqid(),
    'currency' => 'MWK',
    'email' => 'customer@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'mobile' => '265991234567',
    'create_permanent_account' => true,
]);

// Display bank details to customer
$bankDetails = $transfer->data;
```

**Required Fields:**
- `amount` - Payment amount
- `charge_id` - Unique transaction identifier

**Optional Fields:**
- `currency` - Defaults to 'MWK'
- `payment_method` - Defaults to 'mobile_bank_transfer'
- `email` - Customer email
- `first_name` - Customer first name
- `last_name` - Customer last name
- `mobile` - Customer phone
- `create_permanent_account` - Boolean, defaults to true

#### Retrieve Transaction

```php
$transaction = Paychangu::bank_transfer()->retrieveSingleBankTransaction($transactionId);

if ($transaction->data['status'] === 'successful') {
    // Payment confirmed
}
```

### Direct Card Endpoint

Direct card payment processing.

#### Charge Card

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

$charge = Paychangu::direct_card()->chargeACard([
    'card_number' => '4242424242424242',
    'expiry' => '12/30',
    'cvv' => '123',
    'cardholder_name' => 'John Doe',
    'amount' => 2500,
    'currency' => 'MWK',
    'charge_id' => 'CARD-' . uniqid(),
    'redirect_url' => route('payment.card.callback'),
    'email' => 'customer@example.com',
]);

// Handle redirect if required
if (isset($charge->data['redirect_url'])) {
    return redirect($charge->data['redirect_url']);
}
```

**Required Fields:**
- `card_number` - Card PAN
- `expiry` - MM/YY format
- `cvv` - Card security code
- `cardholder_name` - Name on card
- `amount` - Charge amount
- `currency` - Currency code
- `charge_id` - Unique identifier
- `redirect_url` - Callback URL

**Optional Fields:**
- `email` - Customer email for receipt

#### Verify Card Charge

```php
$verification = Paychangu::direct_card()->verifyCardCharge($chargeId);

if ($verification->data['status'] === 'successful') {
    // Payment successful
}
```

#### Refund Card Charge

```php
$refund = Paychangu::direct_card()->refundCardCharge($chargeId);

if ($refund->data['status'] === 'successful') {
    // Refund processed
}
```

## Complete Examples

### Standard Payment Flow

```php
use Mzati\PaychanguSDK\Facades\Paychangu;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function initiate()
    {
        try {
            $transaction = Paychangu::payment()->initiate([
                'amount' => 1000,
                'email' => 'customer@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'tx_ref' => 'TXN-' . time(),
                'callback_url' => route('payment.callback'),
            ]);
            
            return redirect($transaction->data['checkout_url']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    
    public function callback(Request $request)
    {
        $txRef = Paychangu::getTransactionReference($request);
        
        if (!$txRef) {
            return redirect()->route('payment.failed');
        }
        
        $verification = Paychangu::payment()->verify($txRef);
        
        if ($verification->data['status'] === 'successful') {
            // Process order
            return redirect()->route('payment.success');
        }
        
        return redirect()->route('payment.failed');
    }
}
```

### Mobile Money Flow

```php
// Step 1: Get operators
$operators = Paychangu::mobile_money()->getMobileMoneyOperators();

// Step 2: Initiate charge
$charge = Paychangu::mobile_money()->chargeMobileMoney([
    'mobile_money_operator_ref_id' => $operatorRefId,
    'mobile' => '265991234567',
    'amount' => 1000,
    'charge_id' => 'CHARGE-' . time(),
    'email' => 'customer@example.com',
]);

// Step 3: Verify status
$status = Paychangu::mobile_money()->verifyDirectChargeStatus(
    $charge->data['charge_id']
);

if ($status->data['status'] === 'successful') {
    // Payment confirmed
}
```

### Bank Transfer Flow

```php
// Step 1: Initialize transfer
$transfer = Paychangu::bank_transfer()->bankTransfer([
    'amount' => 5000,
    'charge_id' => 'BANK-' . time(),
    'email' => 'customer@example.com',
    'mobile' => '265991234567',
]);

// Display bank details to customer
$bankDetails = $transfer->data;

// Step 2: Check transaction status
$transaction = Paychangu::bank_transfer()->retrieveSingleBankTransaction(
    $transfer->data['transaction_id']
);
```

### Card Payment Flow

```php
// Step 1: Charge card
$charge = Paychangu::direct_card()->chargeACard([
    'card_number' => '4242424242424242',
    'expiry' => '12/30',
    'cvv' => '123',
    'cardholder_name' => 'John Doe',
    'amount' => 2500,
    'currency' => 'MWK',
    'charge_id' => 'CARD-' . time(),
    'redirect_url' => route('payment.card.callback'),
]);

// Step 2: Verify charge
$verification = Paychangu::direct_card()->verifyCardCharge(
    $charge->data['charge_id']
);

if ($verification->data['status'] === 'successful') {
    // Payment successful
}

// Optional: Refund if needed
if ($needsRefund) {
    $refund = Paychangu::direct_card()->refundCardCharge(
        $charge->data['charge_id']
    );
}
```

## Helper Methods

### Extract Transaction Reference

```php
$txRef = Paychangu::getTransactionReference($request);
```

Extracts transaction reference from callback request parameters.

### Environment Checks

```php
$environment = Paychangu::getEnvironment(); // 'test' or 'live'
$isTest = Paychangu::isTest(); // boolean
$isLive = Paychangu::isLive(); // boolean
```

## Error Handling

All API methods throw `PaychanguException` on failure. Always use try-catch blocks:

```php
use Mzati\PaychanguSDK\Exceptions\PaychanguException;

try {
    $transaction = Paychangu::payment()->initiate($data);
} catch (PaychanguException $e) {
    Log::error('Payment failed: ' . $e->getMessage());
    return back()->with('error', 'Payment initiation failed');
}
```

## Logging

The SDK automatically logs to Laravel's default log channel:

- Transaction attempts
- API responses
- Errors and exceptions
- Connection issues

## Supported Currencies

- MWK (Malawian Kwacha)
- USD (United States Dollar)

## Testing

Run tests:

```bash
composer test
```

Run with coverage:

```bash
composer test-coverage
```

Static analysis:

```bash
composer analyse
```

## Security

Report security vulnerabilities to mzatitembo01@gmail.com

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.

## Support

- GitHub: [https://github.com/Mzati1/PaychanguLaravelSDK](https://github.com/Mzati1/PaychanguLaravelSDK)
- Paychangu Docs: [https://paychangu.com](https://paychangu.com)
