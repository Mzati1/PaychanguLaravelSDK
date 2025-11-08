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

## Configuration

Add the following variables to your `.env` file:

```env
PAYCHANGU_SECRET_KEY=sk_live_xxxxx
PAYCHANGU_TEST_KEY=pk_test_xxxxx
PAYCHANGU_ENVIRONMENT=test
PAYCHANGU_BASE_URL=https://api.paychangu.com
PAYCHANGU_CURRENCY=MWK
PAYCHANGU_TIMEOUT=30
```

### Configuration Options

- **PAYCHANGU_SECRET_KEY**: Your Paychangu live API key.
- **PAYCHANGU_TEST_KEY**: Your Paychangu test API key.
- **PAYCHANGU_ENVIRONMENT**: Set to `test` for testing or `live` for production.
- **PAYCHANGU_BASE_URL**: Paychangu API base URL (default: `https://api.paychangu.com`).
- **PAYCHANGU_CURRENCY**: Default currency code (MWK, USD, ZAR, GBP, EUR).
- **PAYCHANGU_TIMEOUT**: Request timeout in seconds (default: 30).

## Usage

### Initiating a Transaction

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

$transaction = Paychangu::payment()->initiate([
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
use Illuminate\Http\Request;

public function handleCallback(Request $request)
{
    $txRef = Paychangu::getTransactionReference($request);

    if (!$txRef) {
        return redirect()->route('payment.failed');
    }

    $verification = Paychangu::payment()->verify($txRef);

    if ($verification->data['status'] === 'successful') {
        // Payment successful
        return redirect()->route('payment.success');
    }

    return redirect()->route('payment.failed');
}
```

## Supported Endpoints

### Payment Endpoint

Access the payment endpoint via `Paychangu::payment()`.

#### Initiate Payment

**Method**: `initiate(array $data)`

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

**Returns**: Object containing `checkout_url` and transaction details.

#### Verify Payment

**Method**: `verify(string $txRef)`

Verifies the status of a transaction using your transaction reference.

**Parameters**:
- `txRef` (string): Your transaction reference

**Returns**: Object containing transaction status and details.

### Mobile Money Endpoint

Access the mobile money endpoint via `Paychangu::mobile_money()`.

#### Get Mobile Money Operators

**Method**: `getMobileMoneyOperators()`

Retrieves a list of supported mobile money operators.

**Returns**: Object containing available mobile money operators with their reference IDs.

#### Charge Mobile Money

**Method**: `chargeMobileMoney(array $data)`

Initiates a mobile money payment charge.

**Required Parameters**:
- `mobile_money_operator_ref_id` (string): Mobile money operator reference ID (get from operators endpoint)
- `mobile` (string): Customer phone number
- `amount` (numeric): Transaction amount
- `charge_id` (string): Unique charge identifier for this transaction

**Optional Parameters**:
- `email` (string): Customer email address
- `first_name` (string): Customer first name
- `last_name` (string): Customer last name

**Returns**: Object containing charge details and status.

#### Verify Direct Charge Status

**Method**: `verifyDirectChargeStatus(string $chargeId)`

Verifies the status of a mobile money direct charge.

**Parameters**:
- `chargeId` (string): The charge ID from the chargeMobileMoney response

**Returns**: Object containing charge status and transaction details.

#### Get Single Charge Details

**Method**: `singleChargeDetails(string $chargeId)`

Retrieves detailed information about a specific mobile money charge.

**Parameters**:
- `chargeId` (string): The charge ID to retrieve details for

**Returns**: Object containing comprehensive charge details including transaction history, status, and metadata.

### Bank Transfer Endpoint

Access the bank transfer endpoint via `Paychangu::bank_transfer()`.

#### Initialize Bank Transfer

**Method**: `bankTransfer(array $data)`

Initiates a bank transfer payment using direct charge.

**Required Parameters**:
- `amount` (string/numeric): The amount of money to be paid
- `charge_id` (string): Unique identifier for the transaction (must be unique for every transaction)

**Optional Parameters**:
- `currency` (string): Currency to charge in (defaults to 'MWK', currently supports 'MWK')
- `payment_method` (string): Payment method (defaults to 'mobile_bank_transfer')
- `email` (string): Customer email address for transaction notifications
- `first_name` (string): Customer first name
- `last_name` (string): Customer last name
- `mobile` (string): Customer mobile phone number
- `create_permanent_account` (boolean): Whether to create a permanent account (defaults to true)

**Returns**: Object containing bank transfer details and payment instructions.

#### Retrieve Single Bank Transaction

**Method**: `retrieveSingleBankTransaction(string $transactionId)`

Retrieves detailed information about a specific bank transfer transaction.

**Parameters**:
- `transactionId` (string): The transaction ID to retrieve details for

**Returns**: Object containing comprehensive transaction details including status, payment information, and history.

### Direct Card Endpoint

Access the direct card endpoint via `Paychangu::direct_card()`.

#### Charge a Card

**Method**: `chargeACard(array $data)`

Charges a card directly using the Paychangu card processing API.

**Required Parameters**:
- `card_number` (string): The card PAN (e.g., 4242424242424242)
- `expiry` (string): Card expiry in MM/YY format (e.g., 12/28)
- `cvv` (string): Card security code (e.g., 123)
- `cardholder_name` (string): Name on card (e.g., John Doe)
- `amount` (string/numeric): Amount to charge
- `currency` (string): 3-letter currency code (e.g., MWK, USD)
- `charge_id` (string): Unique charge ID for tracking
- `redirect_url` (string): URL to redirect after payment

**Optional Parameters**:
- `email` (string): Customer email for receipt

**Returns**: Object containing charge details, payment status, and redirect information.

#### Verify Card Charge

**Method**: `verifyCardCharge(string $chargeId)`

Verifies the status of a card charge transaction.

**Parameters**:
- `chargeId` (string): The unique charge ID for the transaction

**Returns**: Object containing transaction status, payment details, and verification information.

#### Refund Card Charge

**Method**: `refundCardCharge(string $chargeId)`

Processes a refund for a previously charged card transaction.

**Parameters**:
- `chargeId` (string): The unique charge ID for the transaction to refund

**Returns**: Object containing refund status, refund amount, and transaction details.

## Usage Examples

### Standard Payment Flow

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

$transaction = Paychangu::payment()->initiate([
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

### Mobile Money Payment Flow

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

// Step 1: Get available operators
$operators = Paychangu::mobile_money()->getMobileMoneyOperators();
// Select an operator from the response

// Step 2: Initiate mobile money charge
$charge = Paychangu::mobile_money()->chargeMobileMoney([
    'mobile_money_operator_ref_id' => 'operator_ref_id_from_operators',
    'mobile' => '265991234567',
    'amount' => 1000,
    'charge_id' => 'CHARGE-' . time(),
    'email' => 'customer@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe'
]);

// Step 3: Get detailed charge information
$chargeDetails = Paychangu::mobile_money()->singleChargeDetails($charge->data['charge_id']);

// Step 4: Verify charge status (poll or after callback)
$chargeStatus = Paychangu::mobile_money()->verifyDirectChargeStatus($charge->data['charge_id']);

if ($chargeStatus->data['status'] === 'successful') {
    // Payment successful
}
```

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

### Bank Transfer Payment Flow

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

// Step 1: Initialize bank transfer
$bankTransfer = Paychangu::bank_transfer()->bankTransfer([
    'amount' => 5000,
    'charge_id' => 'BANK-' . time(),
    'currency' => 'MWK',
    'email' => 'customer@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'mobile' => '265991234567',
    'create_permanent_account' => true
]);

// Step 2: Retrieve transaction details
$transactionDetails = Paychangu::bank_transfer()->retrieveSingleBankTransaction($bankTransfer->data['transaction_id']);

// Check transaction status
if ($transactionDetails->data['status'] === 'successful') {
    // Payment successful
}
```

### Direct Card Payment Flow

```php
use Mzati\PaychanguSDK\Facades\Paychangu;

// Step 1: Charge the card directly
$cardCharge = Paychangu::direct_card()->chargeACard([
    'card_number' => '4242424242424242',
    'expiry' => '12/30',
    'cvv' => '123',
    'cardholder_name' => 'John Doe',
    'amount' => 2500,
    'currency' => 'MWK',
    'charge_id' => 'CARD-' . time(),
    'redirect_url' => route('payment.card.callback'),
    'email' => 'customer@example.com'
]);

// Step 2: Verify the card charge status
$chargeStatus = Paychangu::direct_card()->verifyCardCharge($cardCharge->data['charge_id']);

if ($chargeStatus->data['status'] === 'successful') {
    // Payment successful
    // Process order, update database, etc.
} else {
    // Payment failed or pending
    // Handle accordingly
}

// Step 3: (Optional) Process refund if needed
if ($needsRefund) {
    $refund = Paychangu::direct_card()->refundCardCharge($cardCharge->data['charge_id']);
    
    if ($refund->data['status'] === 'successful') {
        // Refund processed successfully
    }
}
```
