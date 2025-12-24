<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KashierService
{
    protected string $merchantId;
    protected string $secretKey;
    protected string $apiKey;
    protected string $baseUrl;
    protected bool $isLiveMode;

    public function __construct()
    {
        $config = PaymentSetting::getKashierConfig();

        $this->isLiveMode = $config['mode'] === 'live';
        $this->merchantId = $config['merchant_id'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = 'https://checkout.kashier.io';
    }

    /**
     * Generate order hash for HPP
     * Uses SECRET KEY
     * Amount in POUNDS (not cents)
     */
    public function generateHash(string $orderId, float $amount, string $currency = 'EGP'): string
    {
        // Format amount as string with 2 decimals
        $amountFormatted = number_format($amount, 2, '.', '');

        $path = "/?payment={$this->merchantId}.{$orderId}.{$amountFormatted}.{$currency}";

        return hash_hmac('sha256', $path, $this->apiKey);
    }

    /**
     * Get Kashier checkout URL (HPP)
     */
    public function getCheckoutUrl(
        string $orderId,
        float $amount,
        string $currency,
        string $callbackUrl,
        array $customerData = [],
        array $metadata = [],
        ?string $paymentMethod = null
    ): string {
        $amountFormatted = number_format($amount, 2, '.', '');
        $hash = $this->generateHash($orderId, $amount, $currency);

        $params = [
            'merchantId' => $this->merchantId,
            'orderId' => $orderId,
            'amount' => $amountFormatted,
            'currency' => $currency,
            'hash' => $hash,
            'mode' => $this->isLiveMode ? 'live' : 'test',
            'merchantRedirect' => $callbackUrl,
            'serverWebhook' => route('payment.webhook'),
            'display' => 'ar', // Arabic
            'brandColor' => '#8B5CF6', // Violet theme
        ];

        // Map our payment method to Kashier's supportedPaymentMethods
        if ($paymentMethod) {
            $kashierMethod = match ($paymentMethod) {
                'card' => 'card',
                'meeza' => 'meeza',
                'vodafone_cash' => 'wallet',
                'orange_money' => 'wallet',
                'etisalat_cash' => 'wallet',
                'valu' => 'installment',
                default => null,
            };

            if ($kashierMethod) {
                $params['allowedMethods'] = $kashierMethod;
            }
        }

        // Add customer data
        if (!empty($customerData['name'])) {
            $params['customerName'] = $customerData['name'];
        }
        if (!empty($customerData['email'])) {
            $params['customerEmail'] = $customerData['email'];
        }
        if (!empty($customerData['phone'])) {
            $params['customerPhone'] = $customerData['phone'];
        }

        // Add metadata
        if (!empty($metadata)) {
            $params['metaData'] = json_encode($metadata);
        }

        Log::channel('payments')->info('Kashier checkout URL generated', [
            'order_id' => $orderId,
            'amount' => $amountFormatted,
            'mode' => $params['mode'],
            'payment_method' => $paymentMethod,
        ]);

        return $this->baseUrl . '?' . http_build_query($params);
    }

    /**
     * Validate callback/webhook signature
     * Uses API KEY (not Secret Key!)
     * Per Kashier docs: build query string from all params except signature and mode
     */
    public function validateSignature(array $data): bool
    {
        $signature = $data['signature'] ?? null;

        if (!$signature) {
            Log::warning('Kashier: No signature in callback', $data);
            return false;
        }

        // Build query string from all parameters except signature and mode
        // Per Kashier docs: key1=value1&key2=value2...
        $queryParts = [];
        foreach ($data as $key => $value) {
            if ($key === 'signature' || $key === 'mode') {
                continue;
            }
            $queryParts[] = "{$key}={$value}";
        }

        $queryString = implode('&', $queryParts);
        $calculatedSignature = hash_hmac('sha256', $queryString, $this->apiKey);

        $isValid = hash_equals(strtolower($calculatedSignature), strtolower($signature));

        if (!$isValid) {
            Log::warning('Kashier: Invalid signature', [
                'received' => $signature,
                'calculated' => $calculatedSignature,
                'query_string' => $queryString,
            ]);
        }

        return $isValid;
    }

    /**
     * Test connection to Kashier
     */
    public function testConnection(): array
    {
        if (empty($this->merchantId) || empty($this->secretKey)) {
            return [
                'success' => false,
                'message' => 'بيانات Kashier غير مكتملة',
            ];
        }

        // Generate a test hash to verify secret key works
        $testHash = $this->generateHash('TEST-' . time(), 1.00, 'EGP');

        if (strlen($testHash) === 64) { // SHA256 produces 64 chars
            return [
                'success' => true,
                'message' => 'الاتصال ناجح',
                'mode' => $this->isLiveMode ? 'live' : 'test',
                'merchant_id' => $this->merchantId,
            ];
        }

        return [
            'success' => false,
            'message' => 'فشل في توليد الـ Hash',
        ];
    }

    /**
     * Process refund
     * Uses API KEY
     */
    public function refund(string $transactionId, float $amount, ?string $reason = null): array
    {
        $endpoint = 'https://api.kashier.io/refund';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                        'merchantId' => $this->merchantId,
                        'transactionId' => $transactionId,
                        'amount' => number_format($amount, 2, '.', ''),
                        'reason' => $reason ?? 'Customer request',
                    ]);

            Log::channel('payments')->info('Kashier refund request', [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Refund failed',
                'code' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Kashier refund error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if service is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->merchantId) && !empty($this->secretKey);
    }

    /**
     * Get current mode
     */
    public function getMode(): string
    {
        return $this->isLiveMode ? 'live' : 'test';
    }
}
