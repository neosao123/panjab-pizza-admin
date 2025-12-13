<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\DoorDash;

class DoorDashDriveAPI {
    private string $developerId;
    private string $keyId;
    private string $signingSecret;
    private string $baseUrl;
    private string $mode;

    public function __construct() {
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
        } else {
            $this->developerId = $settings->test_developer_id;
            $this->keyId = $settings->test_key_id;
            $this->signingSecret = $settings->test_signing_secret;
            $this->baseUrl = 'https://openapi.doordash.com/drive/v2';
        }

        // Validate credentials
        if (empty($this->developerId) || empty($this->keyId) || empty($this->signingSecret)) {
            throw new \Exception("DoorDash {$this->mode} credentials are not configured");
        }
    }

    /**
     * Get current mode
     */
    public function getMode(): string {
        return $this->mode;
    }

    /**
     * Check if in live mode
     */
    public function isLive(): bool {
        return $this->mode === 'live';
    }

    /**
     * Check if in sandbox mode
     */
    public function isSandbox(): bool {
        return $this->mode === 'sandbox';
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string {
        $base64Url = strtr(base64_encode($data), '+/', '-_');
        return rtrim($base64Url, '=');
    }

    /**
     * Base64 URL decode
     */
    private function base64UrlDecode(string $base64Url): string {
        return base64_decode(strtr($base64Url, '-_', '+/'));
    }

    /**
     * Generate JWT token
     */
    private function generateJWT(): string {
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
            'dd-ver' => 'DD-JWT-V1'
        ]);

        $payload = json_encode([
            'aud' => 'doordash',
            'iss' => $this->developerId,
            'kid' => $this->keyId,
            'exp' => time() + 300, // 5 minutes expiration
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
    private function makeRequest(string $method, string $endpoint, ?array $data = null): array {
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
     * Create a delivery
     */
    public function createDelivery(array $params): array {
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
     * Get delivery status
     */
    public function getDelivery(string $externalDeliveryId): array {
        return $this->makeRequest('get', "/deliveries/{$externalDeliveryId}");
    }

    /**
     * Update delivery
     */
    public function updateDelivery(string $externalDeliveryId, array $params): array {
        return $this->makeRequest('put', "/deliveries/{$externalDeliveryId}", $params);
    }

    /**
     * Cancel delivery
     */
    public function cancelDelivery(string $externalDeliveryId): array {
        return $this->makeRequest('delete', "/deliveries/{$externalDeliveryId}");
    }
}
