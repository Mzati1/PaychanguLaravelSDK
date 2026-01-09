<?php

declare(strict_types=1);

namespace Paychangu\Laravel\Resources\VirtualAccount;

use InvalidArgumentException;
use Paychangu\Laravel\Resources\BaseResource;

class Customers extends BaseResource
{
    public function createCustomer(array $data): array
    {
        $requiredKeys = ['email', 'first_name', 'last_name'];
        foreach ($requiredKeys as $key) {
            if (empty($data[$key])) {
                throw new InvalidArgumentException("Missing required field: {$key}");
            }
        }

        $response = $this->client->post('customers/create', $data);

        if (isset($response['status']) && $response['status'] === 'success') {
            return [
                'success' => true,
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to create customer',
            'original_response' => $response,
        ];
    }

    public function getAllCustomers(array $query = []): array
    {
        $response = $this->client->get('customers', $query);

        if (isset($response['status']) && $response['status'] === 'success') {
            return [
                'success' => true,
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to fetch customers',
            'original_response' => $response,
        ];
    }

    public function getCustomer(string $customerId): array
    {
        if (empty($customerId)) {
            throw new InvalidArgumentException('Customer ID cannot be empty.');
        }

        $response = $this->client->get("customers/{$customerId}");

        if (isset($response['status']) && $response['status'] === 'success') {
            return [
                'success' => true,
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to fetch customer',
            'original_response' => $response,
        ];
    }

    public function updateCustomer(string $customerId, array $data): array
    {
        if (empty($customerId)) {
            throw new InvalidArgumentException('Customer ID cannot be empty.');
        }

        $response = $this->client->put("customers/{$customerId}", $data);

        if (isset($response['status']) && $response['status'] === 'success') {
            return [
                'success' => true,
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to update customer',
            'original_response' => $response,
        ];
    }

    public function deleteCustomer(string $customerId): array
    {
        if (empty($customerId)) {
            throw new InvalidArgumentException('Customer ID cannot be empty.');
        }

        $response = $this->client->delete("customers/{$customerId}");

        if (isset($response['status']) && $response['status'] === 'success') {
            return [
                'success' => true,
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to delete customer',
            'original_response' => $response,
        ];
    }

    public function createUSAccount(string $customerId): array
    {
        if (empty($customerId)) {
            throw new InvalidArgumentException('Customer ID cannot be empty.');
        }

        $response = $this->client->get("customers/{$customerId}/virtual-account");

        if (isset($response['status']) && $response['status'] === 'success') {
            return [
                'success' => true,
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to create US account',
            'original_response' => $response,
        ];
    }

    public function deactivateUSAccount(string $customerId): array
    {
        if (empty($customerId)) {
            throw new InvalidArgumentException('Customer ID cannot be empty.');
        }

        $response = $this->client->get("customers/{$customerId}/virtual-account/deactivate");

        if (isset($response['status']) && $response['status'] === 'success') {
            return [
                'success' => true,
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to deactivate US account',
            'original_response' => $response,
        ];
    }

    public function reactivateUSAccount(string $customerId): array
    {
        if (empty($customerId)) {
            throw new InvalidArgumentException('Customer ID cannot be empty.');
        }

        $response = $this->client->post("customers/{$customerId}/virtual-account/reactivate");

        if (isset($response['status']) && $response['status'] === 'success') {
            return [
                'success' => true,
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to reactivate US account',
            'original_response' => $response,
        ];
    }

    public function usAccountActivities(string $customerId): array
    {
        if (empty($customerId)) {
            throw new InvalidArgumentException('Customer ID cannot be empty.');
        }

        $response = $this->client->get("customers/{$customerId}/virtual-account/activities");

        if (isset($response['status']) && $response['status'] === 'success') {
            return [
                'success' => true,
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Failed to fetch US account activities',
            'original_response' => $response,
        ];
    }
}
