<?php

namespace Mzati\PaychanguSDK;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mzati\PaychanguSDK\Exceptions\PaychanguException;

class PaychanguService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected int $timeout;

    protected string $environment;

    /**
     * Initialize the Paychangu service
     */
    public function __construct(?string $environment = null)
    {
        $this->environment = $environment ?? config('paychanguConfig.environment', 'test');

        // Select the appropriate API key based on environment
        if ($this->environment === 'live') {
            $this->apiKey = config('paychanguConfig.secret_key');
        } else {
            $this->apiKey = config('paychanguConfig.test_key');
        }

        $this->baseUrl = config('paychanguConfig.base_url', 'https://api.paychangu.com');
        $this->timeout = config('paychanguConfig.timeout', 30);

        if (empty($this->apiKey)) {
            throw new PaychanguException(
                "Paychangu API key is not configured for {$this->environment} mode. " .
                "Please run 'php artisan paychangu:setup' or add PAYCHANGU_" .
                strtoupper($this->environment) . "_KEY to your .env file."
            );
        }
    }

    /**
     * Initiate a payment transaction
     *
     * @param  array  $data  Payment data containing:
     *                       - amount (required): Transaction amount
     *                       - currency (optional): Currency code (defaults to config)
     *                       - email (required): Customer email
     *                       - first_name (optional): Customer first name
     *                       - last_name (optional): Customer last name
     *                       - callback_url (required): URL to redirect after payment
     *                       - return_url (optional): Alternative return URL
     *                       - tx_ref (required): Your unique transaction reference
     *                       - customization (optional): Array with title, description, logo
     *                       - meta (optional): Array of additional metadata
     * @return object Response with checkout_url and other data
     *
     * @throws PaychanguException
     */
    public function initiateTransaction(array $data): object
    {
        // Validate required fields
        $this->validateInitiateData($data);

        // Prepare payload
        $payload = [
            'amount' => (string) $data['amount'], // Paychangu expects string
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
            'environment' => $this->environment,
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
                    'Authorization' => 'Bearer '.$this->apiKey,
                ])
                ->post($this->baseUrl.'/payment', $payload);

            // Handle response
            if (! $response->successful()) {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? $errorData['error'] ?? 'Payment initiation failed';

                Log::error('Paychangu: Transaction initiation failed', [
                    'tx_ref' => $data['tx_ref'],
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'response' => $errorData,
                ]);

                throw new PaychanguException($errorMessage, $response->status());
            }

            $responseData = $response->json();

            Log::info('Paychangu: Transaction initiated successfully', [
                'tx_ref' => $data['tx_ref'],
                'response' => $responseData,
            ]);

            return (object) $responseData;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Paychangu: Connection error', [
                'tx_ref' => $data['tx_ref'],
                'error' => $e->getMessage(),
            ]);

            throw new PaychanguException('Unable to connect to Paychangu. Please try again.', 503, $e);
        } catch (PaychanguException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Paychangu: Unexpected error during initiation', [
                'tx_ref' => $data['tx_ref'],
                'error' => $e->getMessage(),
            ]);

            throw new PaychanguException('An unexpected error occurred: ' . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Verify a transaction
     *
     * @param  string  $txRef  Your transaction reference
     * @return object Verification response with status and transaction details
     *
     * @throws PaychanguException
     */
    public function verifyTransaction(string $txRef): object
    {
        if (empty($txRef)) {
            throw new PaychanguException('Transaction reference is required');
        }

        Log::info('Paychangu: Verifying transaction', [
            'environment' => $this->environment,
            'tx_ref' => $txRef,
        ]);

        try {
            // Make verification request
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->apiKey,
                ])
                ->get($this->baseUrl.'/verify-payment/'.$txRef);

            if (! $response->successful()) {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? $errorData['error'] ?? 'Transaction verification failed';

                Log::error('Paychangu: Verification failed', [
                    'tx_ref' => $txRef,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'response' => $errorData,
                ]);

                throw new PaychanguException($errorMessage, $response->status());
            }

            $responseData = $response->json();

            Log::info('Paychangu: Transaction verified', [
                'tx_ref' => $txRef,
                'status' => $responseData['data']['status'] ?? 'unknown',
                'response' => $responseData,
            ]);

            return (object) $responseData;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Paychangu: Connection error during verification', [
                'tx_ref' => $txRef,
                'error' => $e->getMessage(),
            ]);

            throw new PaychanguException('Unable to connect to Paychangu. Please try again.', 503, $e);
        } catch (PaychanguException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Paychangu: Unexpected error during verification', [
                'tx_ref' => $txRef,
                'error' => $e->getMessage(),
            ]);

            throw new PaychanguException('An unexpected error occurred: ' . $e->getMessage(), 500, $e);
        }
    }

    /**
     * HELPERS
     */

    /**
     * Validate initiate transaction data
     *
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
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new PaychanguException('Invalid email address format');
        }

        // Validate amount
        if (! is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new PaychanguException('Amount must be a positive number');
        }

        // Validate callback URL
        if (! filter_var($data['callback_url'], FILTER_VALIDATE_URL)) {
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
     * @param  \Illuminate\Http\Request  $request
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
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Check if in test mode
     */
    public function isTest(): bool
    {
        return $this->environment === 'test';
    }

    /**
     * Check if in live mode
     */
    public function isLive(): bool
    {
        return $this->environment === 'live';
    }
}
