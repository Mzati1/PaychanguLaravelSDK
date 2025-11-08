<?php

namespace Mzati\PaychanguSDK\Endpoints;

use Mzati\PaychanguSDK\Exceptions\PaychanguException;
use Mzati\PaychanguSDK\Traits\ApiRequester;

class DirectCardEndpoint extends AbstractEndpoint
{
    use ApiRequester;

    /**
     * Charge a card directly
     *
     * @param array $data
     * @return object
     * @throws PaychanguException
     */
    public function chargeACard(array $data)
    {
        $requiredFields = ['card_number', 'expiry', 'cvv', 'cardholder_name', 'amount', 'currency', 'charge_id', 'redirect_url'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("The field '{$field}' is required for card charging.");
            }
        }

        $payload = [
            'card_number' => $data['card_number'],
            'expiry' => $data['expiry'],
            'cvv' => $data['cvv'],
            'cardholder_name' => $data['cardholder_name'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'charge_id' => $data['charge_id'],
            'redirect_url' => $data['redirect_url'],
        ];

        if (isset($data['email']) && !empty($data['email'])) {
            $payload['email'] = $data['email'];
        }

        return $this->service->makeApiRequest('POST', '/charge-card/payments', $payload);
    }
    /**
     * Verify a card charge
     *
     * @param string $chargeId
     * @return object
     * @throws PaychanguException
     */
    public function verifyCardCharge(string $chargeId)
    {
        if (empty($chargeId)) {
            throw new \InvalidArgumentException('Charge ID is required for verification.');
        }

        return $this->makeApiRequest('GET', "/charge-card/verify/{$chargeId}");
    }

    /**
     * Refund a card charge
     *
     * @param string $chargeId
     * @return object
     * @throws PaychanguException
     */
    public function refundCardCharge(string $chargeId)
    {
        if (empty($chargeId)) {
            throw new \InvalidArgumentException('Charge ID is required for refund.');
        }

        return $this->makeApiRequest('POST', "/charge-card/refund/{$chargeId}");
    }
}