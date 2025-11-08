<?php

namespace Mzati\PaychanguSDK\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mzati\PaychanguSDK\Exceptions\PaychanguException;

trait ApiRequester
{
    /**
     * Make an API request to Paychangu.
     *
     * @param  string  $method  HTTP method (GET, POST, etc.)
     * @param  string  $endpoint  API endpoint
     * @param  array  $payload  Request data
     * @return object Response data
     *
     * @throws PaychanguException
     */
    protected function makeApiRequest(string $method, string $endpoint, array $payload = []): object
    {
        $url = $this->baseUrl.$endpoint;

        try {
            $method = strtolower($method);
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$this->apiKey,
                ])
                ->{$method}($url, $payload);

            if (! $response->successful()) {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? $errorData['error'] ?? 'API request failed';

                Log::error('Paychangu: API request failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);

                throw new PaychanguException($errorMessage, $response->status());
            }

            return (object) $response->json();

        } catch (\Exception $e) {
            Log::error('Paychangu: Unexpected error during API request', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new PaychanguException('An unexpected error occurred: '.$e->getMessage(), 500, $e);
        }
    }
}
