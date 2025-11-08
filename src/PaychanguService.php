<?php

namespace Mzati\PaychanguSDK;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mzati\PaychanguSDK\Exceptions\PaychanguException;
use Mzati\PaychanguSDK\Traits\ApiRequester;

class PaychanguService
{
    use ApiRequester;

    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected string $environment;

    /**
     * @var array<string, object>
     */
    protected array $endpoints = [];

    /**
     * Initialize the Paychangu service
     */
    public function __construct(?string $environment = null)
    {
        $this->environment = $environment ?? config('paychangu.environment', 'test');

        if ($this->environment === 'live') {
            $this->apiKey = config('paychangu.secret_key');
        } else {
            $this->apiKey = config('paychangu.test_key');
        }

        $this->baseUrl = config('paychangu.base_url', 'https://api.paychangu.com');
        $this->timeout = config('paychangu.timeout', 30);

        if (empty($this->apiKey)) {
            throw new PaychanguException(
                "Paychangu API key is not configured for {$this->environment} mode. ".
                "Please run 'php artisan paychangu:setup' or add PAYCHANGU_".
                strtoupper($this->environment).'_KEY to your .env file.'
            );
        }
    }

    /**
     * Dynamically handle calls to endpoint classes.
     *
     * @param  string  $name  Endpoint name
     * @return object Endpoint instance
     *
     * @throws \Exception
     */
    public function __get(string $name): object
    {
        $class = 'Mzati\\PaychanguSDK\\Endpoints\\'.ucfirst($name).'Endpoint';

        if (! class_exists($class)) {
            throw new \Exception("Endpoint [{$name}] does not exist.");
        }

        if (! isset($this->endpoints[$name])) {
            $this->endpoints[$name] = new $class($this);
        }

        return $this->endpoints[$name];
    }

    /**
     * Dynamically handle calls to the API requester.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return object
     *
     * @throws \Exception
     */
    public function __call(string $method, array $parameters): object
    {
        if (method_exists($this, $method)) {
            return $this->{$method}(...$parameters);
        }

        if (property_exists($this, $method)) {
            return $this->{$method};
        }

        return $this->makeApiRequest(...$parameters);
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
