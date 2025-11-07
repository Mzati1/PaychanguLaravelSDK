<?php

namespace Mzati\PaychanguSDK;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mzati\PaychanguSDK\Exceptions\PaychanguException;

class PaychanguService
{
    protected string $secretKey;
    protected string $baseUrl;
    protected int $timeout;

    /**
     * Initialize the Paychangu service
     *
     * @param string $secretKey
     * @param string $environment
     */
    public function __construct(string $secretKey, string $environment = 'test')
    {
        $this->secretKey = $secretKey;

        $this->baseUrl = $environment === 'live'
            ? config('paychanguConfig.live_url', config('paychanguConfig.base_url'))
            : config('paychanguConfig.test_url', config('paychanguConfig.base_url'));
        $this->timeout = config('paychanguConfig.timeout', 30);

        if (empty($this->secretKey)) {
            throw new PaychanguException('Paychangu secret key is not configured');
        }
    }

    /**
     * Initiate a payment transaction
     *
     * @param array $data Payment data containing:
     *  - amount (required): Transaction amount
     *  - currency (optional): Currency code (defaults to config)
     *  - email (required): Customer email
     *  - first_name (optional): Customer first name
     *  - last_name (optional): Customer last name
     *  - callback_url (required): URL to redirect after payment
     *  - return_url (optional): Alternative return URL
     *  - tx_ref (required): Your unique transaction reference
     *  - customization (optional): Array with title, description, logo
     *  - meta (optional): Array of additional metadata
     *
     * @return object Response with checkout_url and other data
     * @throws PaychanguException
     */

    public function initiateTransaction(array $data): object
    {
        // Validate required fields
        $this->validateInitiateData($data);

        // Prepare payload
        $payload = [
            'amount' => (float) $data['amount'],
            'currency' => $data['currency'] ?? config('paychanguConfig.currency', 'MWK'),
            'email' => $data['email'],
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'callback_url' => $data['callback_url'],
            'return_url' => $data['return_url'] ?? $data['callback_url'],
            'tx_ref' => $data['tx_ref'],
        ];

        // Add optional fields if provided
        if (isset($data['customization'])) {
            $payload['customization'] = $data['customization'];
        }

        if (isset($data['meta'])) {
            $payload['meta'] = $data['meta'];
        }

        // Log the initiation attempt
        Log::info('Paychangu: Initiating transaction', [
            'tx_ref' => $data['tx_ref'],
            'amount' => $payload['amount'],
            'email' => $payload['email'],
        ]);

        try {
            // Make API request
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->secretKey,
                ])
                ->post($this->baseUrl . '/payment', $payload);

            // Handle response
            if (!$response->successful()) {
                $errorMessage = $response->json()['message'] ?? 'Payment initiation failed';

                Log::error('Paychangu: Transaction initiation failed', [
                    'tx_ref' => $data['tx_ref'],
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);

                throw new PaychanguException($errorMessage, $response->status());
            }

            $responseData = $response->json();

            Log::info('Paychangu: Transaction initiated successfully', [
                'tx_ref' => $data['tx_ref'],
            ]);

            return (object) $responseData;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Paychangu: Connection error', [
                'tx_ref' => $data['tx_ref'],
                'error' => $e->getMessage(),
            ]);

            throw new PaychanguException('Unable to connect to Paychangu. Please try again.', 503);
        }
    }

    /**
     * Verify a transaction
     *
     * @param string $txRef Your transaction reference
     * @return object Verification response with status and transaction details
     * @throws PaychanguException
     */
    public function verifyTransaction(string $txRef): object
    {
        if (empty($txRef)) {
            throw new PaychanguException('Transaction reference is required');
        }

        Log::info('Paychangu: Verifying transaction', ['tx_ref' => $txRef]);

        try {
            // Make verification request
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->secretKey,
                ])
                ->get($this->baseUrl . '/verify-payment/' . $txRef);

            if (!$response->successful()) {
                $errorMessage = $response->json()['message'] ?? 'Transaction verification failed';

                Log::error('Paychangu: Verification failed', [
                    'tx_ref' => $txRef,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);

                throw new PaychanguException($errorMessage, $response->status());
            }

            $responseData = $response->json();

            Log::info('Paychangu: Transaction verified', [
                'tx_ref' => $txRef,
                'status' => $responseData['data']['status'] ?? 'unknown',
                'payload' => $responseData,
            ]);

            return (object) $responseData;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Paychangu: Connection error during verification', [
                'tx_ref' => $txRef,
                'error' => $e->getMessage(),
            ]);

            throw new PaychanguException('Unable to connect to Paychangu. Please try again.', 503);
        }
    }


    /**
     * HELPERS
     */


    /**
     * Validate initiate transaction data
     *
     * @param array $data
     * @throws PaychanguException
     */
    protected function validateInitiateData(array $data): void
    {
        $required = ['amount', 'email', 'callback_url', 'tx_ref'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new PaychanguException("The {$field} field is required");
            }
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new PaychanguException('Invalid email address format');
        }

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new PaychanguException('Amount must be a positive number');
        }

        // Validate callback URL
        if (!filter_var($data['callback_url'], FILTER_VALIDATE_URL)) {
            throw new PaychanguException('Invalid callback URL format');
        }

        // Validate tx_ref format (optional but recommended)
        if (strlen($data['tx_ref']) < 3) {
            throw new PaychanguException('Transaction reference must be at least 3 characters long');
        }
    }

    /**
     * Get transaction reference from callback request
     * Helper method to extract tx_ref from callback
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function getTransactionReference($request): ?string
    {
        return $request->input('tx_ref')
            ?? $request->input('transaction_id')
            ?? $request->input('reference')
            ?? $request->query('tx_ref')
            ?? $request->query('transaction_id')
            ?? $request->query('reference');
    }

    /**
     * Get the current environment
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        // Compare against the resolved live URL, falling back to base_url if
        // live_url isn't configured so behaviour is consistent with
        // constructor resolution above.
        $liveUrl = config('paychanguConfig.live_url', config('paychanguConfig.base_url'));

        return $this->baseUrl === $liveUrl ? 'live' : 'test';
    }

    /**
     * Check if in test mode
     *
     * @return bool
     */
    public function istest(): bool
    {
        return $this->getEnvironment() === 'test';
    }
}
