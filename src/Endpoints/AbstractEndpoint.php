<?php

namespace Mzati\PaychanguSDK\Endpoints;

use Mzati\PaychanguSDK\PaychanguService;

abstract class AbstractEndpoint
{
    protected PaychanguService $service;

    public function __construct(PaychanguService $service)
    {
        $this->service = $service;
    }
}