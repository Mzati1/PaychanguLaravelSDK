<?php

namespace Mzati\PaychanguSDK\Endpoints;

use Mzati\PaychanguSDK\Traits\ApiRequester;

class MobileMoneyEndpoint extends AbstractEndpoint
{
    use ApiRequester;

    /**
     * Get supported mobile money operators
     *
     * @return object
     * @throws \Exception
     */
    public function getMobileMoneyOperators()
    {
        return $this->makeApiRequest('GET', '/mobile-money');
    }

    /**
     * Charge mobile money
     *
     * @param array $data
     * @return object
     * @throws \Exception
     */
    public function chargeMobileMoney(array $data)
    {
        $requiredFields = ['mobile_money_operator_ref_id', 'mobile', 'amount', 'charge_id'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        return $this->makeApiRequest('POST', '/mobile-money/payments/initialize', $data);
    }

    /**
     * Verify direct charge status
     *
     * @param string $chargeId
     * @return object
     * @throws \Exception
     */
    public function verifyDirectChargeStatus(string $chargeId)
    {
        return $this->makeApiRequest('GET', "/mobile-money/payments/{$chargeId}/verify");
    }

    /**
     * Get single charge details
     *
     * @param string $chargeId
     * @return object
     * @throws \Exception
     */
    public function singleMobileChargeDetails(string $chargeId)
    {
        return $this->makeApiRequest('GET', "/mobile-money/payments/{$chargeId}/details");
    }
}