<?php

namespace Mzati\PaychanguSDK\Endpoints;

use Mzati\PaychanguSDK\Traits\ApiRequester;

class BankTransferEndpoint extends AbstractEndpoint
{
    use ApiRequester;

    /**
     * Initialize bank transfer payment
     *
     * @return object
     *
     * @throws \Exception
     */
    public function bankTransfer(array $data)
    {
        $requiredFields = ['amount', 'charge_id'];

        foreach ($requiredFields as $field) {
            if (! isset($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Set defaults if not provided
        $data['currency'] = $data['currency'] ?? 'MWK';
        $data['payment_method'] = $data['payment_method'] ?? 'mobile_bank_transfer';

        return $this->makeApiRequest('POST', '/direct-charge/payments/initialize', $data);
    }

    /**
     * Retrieve single bank transaction details
     *
     * @return object
     *
     * @throws \Exception
     */
    public function retrieveSingleBankTransaction(string $transactionId)
    {
        return $this->makeApiRequest('GET', "/direct-charge/transactions/{$transactionId}/details");
    }
}
