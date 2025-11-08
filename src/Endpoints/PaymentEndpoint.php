<?php

namespace Mzati\PaychanguSDK\Endpoints;

use Mzati\PaychanguSDK\Exceptions\PaychanguException;

class PaymentEndpoint extends AbstractEndpoint
{
    /**
     * Initiate a payment transaction.
     *
     * @param  array  $data  Payment data
     * @return object Response data
     *
     * @throws PaychanguException
     */
    public function initiate(array $data): object
    {
        $this->validateInitiateData($data);

        $payload = [
            'amount' => (string) $data['amount'],
            'currency' => $data['currency'] ?? config('paychangu.currency', 'MWK'),
            'email' => $data['email'],
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'callback_url' => $data['callback_url'],
            'return_url' => $data['return_url'] ?? $data['callback_url'],
            'tx_ref' => $data['tx_ref'],
        ];

        if (isset($data['customization'])) {
            $payload['customization'] = $data['customization'];
        }

        if (isset($data['meta'])) {
            $payload['meta'] = $data['meta'];
        }

        return $this->service->makeApiRequest('post', '/payment', $payload);
    }

    /**
     * Verify a transaction.
     *
     * @param  string  $txRef  Transaction reference
     * @return object Response data
     *
     * @throws PaychanguException
     */
    public function verify(string $txRef): object
    {
        if (empty($txRef)) {
            throw new PaychanguException('Transaction reference is required');
        }

        return $this->service->makeApiRequest('get', '/verify-payment/'.$txRef);
    }

    /**
     * Validate initiate transaction data.
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

        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new PaychanguException('Invalid email address format');
        }

        if (! is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new PaychanguException('Amount must be a positive number');
        }

        if (! filter_var($data['callback_url'], FILTER_VALIDATE_URL)) {
            throw new PaychanguException('Invalid callback URL format');
        }

        if (strlen($data['tx_ref']) < 3) {
            throw new PaychanguException('Transaction reference must be at least 3 characters long');
        }
    }
}
