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
        // Validate base URL
        if (empty($this->baseUrl)) {
            throw new PaychanguException('Base URL is not configured', 500);
        }

        // Validate endpoint
        if (empty($endpoint)) {
            throw new PaychanguException('API endpoint cannot be empty', 500);
        }

        // Ensure proper URL construction with forward slashes
        $baseUrl = rtrim($this->baseUrl, '/');
        $endpoint = ltrim($endpoint, '/');
        $url = $baseUrl.'/'.$endpoint;

        // Validate final URL format
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            Log::error('Paychangu: Invalid URL constructed', [
                'base_url' => $this->baseUrl,
                'endpoint' => $endpoint,
                'final_url' => $url,
            ]);
            throw new PaychanguException("Invalid API URL constructed: {$url}", 500);
        }

        Log::debug('Paychangu: API request initiated', [
            'url' => $url,
            'method' => $method,
            'endpoint' => $endpoint,
        ]);

        try {
            $method = strtoupper($method);

            $httpClient = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$this->apiKey,
                ]);

            // Handle GET requests differently (no body, use query parameters)
            if ($method === 'GET') {
                $response = $httpClient->get($url, $payload);
            } else {
                // POST, PUT, PATCH, DELETE can have body
                $response = $httpClient->{strtolower($method)}($url, $payload);
            }

            if (! $response->successful()) {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? $errorData['error'] ?? 'API request failed';

                Log::error('Paychangu: API request failed', [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'response' => $errorData,
                ]);

                throw new PaychanguException($errorMessage, $response->status());
            }

            return (object) $response->json();

        } catch (PaychanguException $e) {
            // Re-throw PaychanguException as-is
            throw $e;
        } catch (\Exception $e) {
            Log::error('Paychangu: Unexpected error during API request', [
                'endpoint' => $endpoint,
                'method' => $method,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new PaychanguException('An unexpected error occurred: '.$e->getMessage(), 500, $e);
        }
    }
}
