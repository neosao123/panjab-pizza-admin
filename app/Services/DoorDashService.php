<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\DoorDash;

class DoorDashService
{
    private string $developerId;
    private string $keyId;
    private string $signingSecret;
    private string $baseUrl;
    private string $mode;
    private string $businessUrl;

    public function __construct()
    {
        $settings = DoorDash::first();

        if (!$settings) {
            throw new \Exception('DoorDash settings not found in database');
        }

        $this->mode = $settings->mode ?? 'sandbox';

        if ($this->mode === 'live') {
            $this->developerId = $settings->live_developer_id;
            $this->keyId = $settings->live_key_id;
            $this->signingSecret = $settings->live_signing_secret;
            $this->baseUrl = 'https://openapi.doordash.com/drive/v2';
            $this->businessUrl = 'https://openapi.doordash.com/developer/v1';
        } else {
            $this->developerId = $settings->test_developer_id;
            $this->keyId = $settings->test_key_id;
            $this->signingSecret = $settings->test_signing_secret;
            $this->baseUrl = 'https://openapi.doordash.com/drive/v2';
            $this->businessUrl = 'https://openapi.doordash.com/developer/v1';
        }

        if (empty($this->developerId) || empty($this->keyId) || empty($this->signingSecret)) {
            throw new \Exception("DoorDash {$this->mode} credentials are not configured");
        }
    }

    /**
     * Get current mode
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Check if in live mode
     */
    public function isLive(): bool
    {
        return $this->mode === 'live';
    }

    /**
     * Check if in sandbox mode
     */
    public function isSandbox(): bool
    {
        return $this->mode === 'sandbox';
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        $base64Url = strtr(base64_encode($data), '+/', '-_');
        return rtrim($base64Url, '=');
    }

    /**
     * Base64 URL decode
     */
    private function base64UrlDecode(string $base64Url): string
    {
        return base64_decode(strtr($base64Url, '-_', '+/'));
    }

    /**
     * Generate JWT token
     */
    private function generateJWT(): string
    {
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
            'dd-ver' => 'DD-JWT-V1'
        ]);

        $payload = json_encode([
            'aud' => 'doordash',
            'iss' => $this->developerId,
            'kid' => $this->keyId,
            'exp' => time() + 300,
            'iat' => time()
        ]);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            $this->base64UrlDecode($this->signingSecret),
            true
        );

        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Make API request using Laravel HTTP Client
     */
    public function makeRequest(string $method, string $endpoint, ?array $data = null): array
    {
        try {
            $jwt = $this->generateJWT();
            $url = $this->baseUrl . $endpoint;

            Log::info("DoorDash API Request [{$this->mode}]", [
                'method' => $method,
                'url' => $url,
                'data' => $data
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/json',
            ])->$method($url, $data);

            $result = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $response->json(),
                'mode' => $this->mode
            ];

            Log::info("DoorDash API Response [{$this->mode}]", $result);

            return $result;
        } catch (\Exception $e) {
            Log::error("DoorDash API Error [{$this->mode}]: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'mode' => $this->mode
            ];
        }
    }

    /**
     * Make API request using Laravel HTTP Client
     */
    public function makeBusinessRequest(string $method, string $endpoint, ?array $data = null): array
    {
        try {
            $jwt = $this->generateJWT();
            $url = $this->businessUrl . $endpoint;

            Log::info("DoorDash API Request [{$this->mode}]", [
                'method' => $method,
                'url' => $url,
                'data' => $data
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/json',
            ])->$method($url, $data);

            $result = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $response->json(),
                'mode' => $this->mode
            ];

            Log::info("DoorDash API Response [{$this->mode}]", $result);

            return $result;
        } catch (\Exception $e) {
            Log::error("DoorDash API Error [{$this->mode}]: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'mode' => $this->mode
            ];
        }
    }
    /**
     * 1. Create Quote
     */
    public function createQuote(array $params): array
    {
        $requiredFields = [
            'external_delivery_id',
            'pickup_address',
            'pickup_phone_number',
            'dropoff_address',
            'dropoff_phone_number',
            'order_value'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($params[$field])) {
                return [
                    'success' => false,
                    'error' => "Missing required field: $field",
                    'mode' => $this->mode
                ];
            }
        }

        return $this->makeRequest('post', '/quotes', $params);
    }

    /**
     * 2. Accept Quote
     */
    public function acceptQuote(string $externalDeliveryId, ?array $params = null): array
    {
        return $this->makeRequest('post', "/quotes/{$externalDeliveryId}/accept", $params);
    }

    /**
     * 3. Create Delivery
     */
    public function createDelivery(array $params): array
    {
        $requiredFields = [
            'external_delivery_id',
            'pickup_address',
            'pickup_phone_number',
            'dropoff_address',
            'dropoff_phone_number',
            'order_value'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($params[$field])) {
                return [
                    'success' => false,
                    'error' => "Missing required field: $field",
                    'mode' => $this->mode
                ];
            }
        }

        return $this->makeRequest('post', '/deliveries', $params);
    }

    /**
     * 4. Get Delivery Status
     */
    public function getDelivery(string $externalDeliveryId): array
    {
        return $this->makeRequest('get', "/deliveries/{$externalDeliveryId}");
    }

    /**
     * 5. Update Delivery
     */
    public function updateDelivery(string $externalDeliveryId, array $params): array
    {
        return $this->makeRequest('patch', "/deliveries/{$externalDeliveryId}", $params);
    }

    /**
     * 6. Cancel Delivery
     */
    public function cancelDelivery(string $externalDeliveryId): array
    {
        return $this->makeRequest('put', "/deliveries/{$externalDeliveryId}/cancel");
    }



    // ========================================
    // BUSINESS APIS
    // ========================================


    /**
     * 1. Create Business
     * Note: "default" cannot be used as external_business_id (reserved)
     */
    public function createBusiness(array $params): array
    {
        Log::info('DoorDash Create Business Request', [
            'params' => $params,
            'mode' => $this->mode
        ]);

        $requiredFields = ['external_business_id', 'name'];

        foreach ($requiredFields as $field) {
            if (!isset($params[$field])) {
                $error = "Missing required field: $field";
                Log::warning('DoorDash Create Business Validation Failed', [
                    'error' => $error,
                    'params' => $params
                ]);
                return [
                    'success' => false,
                    'error' => $error,
                    'mode' => $this->mode
                ];
            }
        }

        // Validate that external_business_id is not "default"
        if ($params['external_business_id'] === 'default') {
            $error = 'Cannot use "default" as external_business_id (reserved)';
            Log::warning('DoorDash Create Business Validation Failed', [
                'error' => $error,
                'params' => $params
            ]);
            return [
                'success' => false,
                'error' => $error,
                'mode' => $this->mode
            ];
        }

        try {
            $response = $this->makeBusinessRequest('post', '/businesses', $params, 'developer');
            Log::info('DoorDash Create Business Response', [
                'response' => $response,
                'params' => $params
            ]);
            return $response;
        } catch (\Exception $ex) {
            Log::error('DoorDash Create Business Exception', [
                'error' => $ex->getMessage(),
                'params' => $params
            ]);
            return [
                'success' => false,
                'error' => $ex->getMessage(),
                'mode' => $this->mode
            ];
        }
    }




    /**
     * 2. Create Store for a Business
     * POST /businesses/{external_business_id}/stores
     */
    public function createStore(array $params): array
    {
        Log::info('DoorDash Create Store Request', [
            'params' => $params,
            'mode' => $this->mode
        ]);

        $requiredFields = ['external_business_id', 'external_store_id', 'name', 'phone_number', 'address'];

        foreach ($requiredFields as $field) {
            if (!isset($params[$field])) {
                $error = "Missing required field: $field";
                Log::warning('DoorDash Create Store Validation Failed', [
                    'error' => $error,
                    'params' => $params
                ]);
                return [
                    'success' => false,
                    'error' => $error,
                    'mode' => $this->mode
                ];
            }
        }

        // Validate external_store_id is not "default"
        if ($params['external_store_id'] === 'default') {
            $error = 'Cannot use "default" as external_store_id (reserved)';
            Log::warning('DoorDash Create Store Validation Failed', [
                'error' => $error,
                'params' => $params
            ]);
            return [
                'success' => false,
                'error' => $error,
                'mode' => $this->mode
            ];
        }

        try {
            $endpoint = "/businesses/{$params['external_business_id']}/stores";

            // Remove external_business_id from payload since it's in the URL
            $payload = $params;
            unset($payload['external_business_id']);

            // Fixed: Remove the 4th parameter
            $response = $this->makeBusinessRequest('post', $endpoint, $payload);

            Log::info('DoorDash Create Store Response', [
                'response' => $response,
                'params' => $params
            ]);

            return $response;
        } catch (\Exception $ex) {
            Log::error('DoorDash Create Store Exception', [
                'error' => $ex->getMessage(),
                'params' => $params,
                'trace' => $ex->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => $ex->getMessage(),
                'mode' => $this->mode
            ];
        }
    }

    /**
     * 3. Update Store for a Business
     * PUT /businesses/{external_business_id}/stores/{external_store_id}
     */
    public function updateStore(array $params): array
    {
        Log::info('DoorDash Update Store Request', [
            'params' => $params,
            'mode' => $this->mode
        ]);

        $requiredFields = ['external_business_id', 'external_store_id'];

        foreach ($requiredFields as $field) {
            if (!isset($params[$field])) {
                $error = "Missing required field: $field";
                Log::warning('DoorDash Update Store Validation Failed', [
                    'error' => $error,
                    'params' => $params
                ]);
                return [
                    'success' => false,
                    'error' => $error,
                    'mode' => $this->mode
                ];
            }
        }

        // Removed: dd($params['external_business_id']);

        try {
            $endpoint = "/businesses/{$params['external_business_id']}/stores/{$params['external_store_id']}";

            $payload = $params;
            unset(
                $payload['external_business_id'],
                $payload['external_store_id']
            );

            // Fixed: Remove the 4th parameter
            $response = $this->makeBusinessRequest('patch', $endpoint, $payload);

            Log::info('DoorDash Update Store Response', [
                'response' => $response,
                'params' => $params
            ]);

            return $response;
        } catch (\Exception $ex) {
            Log::error('DoorDash Update Store Exception', [
                'error' => $ex->getMessage(),
                'params' => $params,
                'trace' => $ex->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $ex->getMessage(),
                'mode' => $this->mode
            ];
        }
    }


    /**
     * Get Store details from DoorDash API
     *
     * @param string $externalBusinessId
     * @param string $externalStoreId
     * @return array
     */
    public function getStore(string $externalBusinessId, string $externalStoreId): array
    {
        try {
            $endpoint = "/businesses/{$externalBusinessId}/stores/{$externalStoreId}";

            Log::info('DoorDash Service - Getting Store', [
                'endpoint' => $endpoint,
                'external_business_id' => $externalBusinessId,
                'external_store_id' => $externalStoreId
            ]);

            $result = $this->makeBusinessRequest('get', $endpoint);

            Log::info('DoorDash Service - Get Store Result', [
                'external_business_id' => $externalBusinessId,
                'external_store_id' => $externalStoreId,
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('DoorDash Service - Get Store Exception', [
                'external_business_id' => $externalBusinessId,
                'external_store_id' => $externalStoreId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
}
