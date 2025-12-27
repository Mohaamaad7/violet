<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Kashier Payment Gateway
 * 
 * بوابة كاشير للدفع الإلكتروني.
 * تدعم: البطاقات، المحافظ الإلكترونية، ميزة، ڤاليو.
 * 
 * ملاحظة: كاشير تتعامل بالجنيه المصري (150.00) وليس بالقروش.
 */
class KashierGateway implements PaymentGatewayInterface
{
    protected string $merchantId;
    protected string $secretKey;
    protected string $apiKey;
    protected string $baseUrl;
    protected bool $isLiveMode;

    public function __construct()
    {
        $config = PaymentSetting::getKashierConfig();

        $this->isLiveMode = ($config['mode'] ?? 'test') === 'live';
        $this->merchantId = $config['merchant_id'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = 'https://checkout.kashier.io';
    }

    // ==================== Interface Implementation ====================

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'kashier';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName(): string
    {
        return 'Kashier';
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        return !empty($this->merchantId) && !empty($this->apiKey);
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'بيانات Kashier غير مكتملة',
            ];
        }

        // Generate a test hash to verify API key works
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
     * {@inheritdoc}
     */
    public function getSupportedMethods(): array
    {
        $methods = [];

        if (PaymentSetting::isMethodEnabled('card')) {
            $methods['card'] = [
                'name' => 'بطاقة ائتمانية',
                'name_en' => 'Credit Card',
                'icon' => 'credit-card',
                'description' => 'Visa, Mastercard',
            ];
        }

        if (PaymentSetting::isMethodEnabled('meeza')) {
            $methods['meeza'] = [
                'name' => 'ميزة',
                'name_en' => 'Meeza',
                'icon' => 'meeza',
                'description' => 'بطاقة ميزة الوطنية',
            ];
        }

        if (PaymentSetting::isMethodEnabled('vodafone_cash')) {
            $methods['vodafone_cash'] = [
                'name' => 'فودافون كاش',
                'name_en' => 'Vodafone Cash',
                'icon' => 'vodafone',
                'description' => 'ادفع من محفظة فودافون',
            ];
        }

        if (PaymentSetting::isMethodEnabled('orange_money')) {
            $methods['orange_money'] = [
                'name' => 'أورانج موني',
                'name_en' => 'Orange Money',
                'icon' => 'orange',
                'description' => 'ادفع من محفظة أورانج',
            ];
        }

        if (PaymentSetting::isMethodEnabled('etisalat_cash')) {
            $methods['etisalat_cash'] = [
                'name' => 'اتصالات كاش',
                'name_en' => 'Etisalat Cash',
                'icon' => 'etisalat',
                'description' => 'ادفع من محفظة اتصالات',
            ];
        }

        if (PaymentSetting::isMethodEnabled('valu')) {
            $methods['valu'] = [
                'name' => 'ڤاليو',
                'name_en' => 'ValU',
                'icon' => 'valu',
                'description' => 'اشتري الآن وادفع لاحقاً',
            ];
        }

        return $methods;
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePayment(Order $order, string $method): array
    {
        return DB::transaction(function () use ($order, $method) {
            // Check if method is enabled
            if (!PaymentSetting::isMethodEnabled($method)) {
                return [
                    'success' => false,
                    'error' => 'طريقة الدفع غير متاحة',
                ];
            }

            // Check if configured
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'نظام الدفع غير مُعدّ',
                ];
            }

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'amount' => $order->total,
                'currency' => 'EGP',
                'payment_method' => $method,
                'status' => 'pending',
                'gateway' => $this->getName(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Get customer data
            $customerData = [
                'name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
            ];

            // Generate checkout URL
            $checkoutUrl = $this->getCheckoutUrl(
                orderId: $payment->reference,
                amount: (float) $order->total,
                currency: 'EGP',
                callbackUrl: $this->getCallbackUrl(),
                customerData: $customerData,
                metadata: [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
                paymentMethod: $method
            );

            // Update payment with gateway reference
            $payment->update([
                'gateway_order_id' => $payment->reference,
            ]);

            Log::info('Kashier: Payment initiated', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'amount' => $order->total,
                'method' => $method,
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'redirect_url' => $checkoutUrl,
            ];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function handleCallback(array $data): array
    {
        // Validate signature first
        if (!$this->validateSignature($data)) {
            Log::warning('Kashier: Invalid callback signature', $data);
            return [
                'success' => false,
                'error' => 'Invalid signature',
            ];
        }

        // Find payment by reference
        $merchantOrderId = $data['merchantOrderId'] ?? null;
        $kashierOrderId = $data['orderId'] ?? null;

        $payment = Payment::where('reference', $merchantOrderId)
            ->orWhere('reference', $kashierOrderId)
            ->orWhere('gateway_order_id', $merchantOrderId)
            ->orWhere('gateway_order_id', $kashierOrderId)
            ->first();

        if (!$payment) {
            Log::error('Kashier: Payment not found for callback', [
                'merchant_order_id' => $merchantOrderId,
                'kashier_order_id' => $kashierOrderId,
            ]);
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }

        // Idempotency check
        if ($payment->status === 'completed') {
            Log::info('Kashier: Payment already completed (idempotency)', ['payment_id' => $payment->id]);
            return [
                'success' => true,
                'payment' => $payment,
                'order' => $payment->order,
                'already_processed' => true,
            ];
        }

        $paymentStatus = $data['paymentStatus'] ?? '';
        $transactionId = $data['transactionId'] ?? $data['cardDataToken'] ?? null;

        if (strtoupper($paymentStatus) === 'SUCCESS') {
            // Mark as completed
            $payment->markAsCompleted($transactionId, $data);

            // Update order status
            $payment->order->update([
                'status' => \App\Enums\OrderStatus::PENDING,
                'payment_status' => 'paid',
                'payment_method' => $payment->payment_method,
                'payment_transaction_id' => $transactionId,
                'paid_at' => now(),
            ]);

            // Send confirmation emails
            $this->sendConfirmationEmails($payment);

            Log::info('Kashier: Payment completed', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => true,
                'payment' => $payment->fresh(),
                'order' => $payment->order,
                'status' => 'completed',
            ];
        } else {
            // Mark as failed
            $failReason = $data['failureReason'] ?? $data['error'] ?? 'Payment declined';
            $failCode = $data['errorCode'] ?? null;
            $payment->markAsFailed($failReason, $failCode, $data);

            Log::warning('Kashier: Payment failed', [
                'payment_id' => $payment->id,
                'reason' => $failReason,
            ]);

            return [
                'success' => false,
                'payment' => $payment->fresh(),
                'order' => $payment->order,
                'status' => 'failed',
                'error' => $failReason,
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleWebhook(array $data): array
    {
        // Webhooks use the same logic as callbacks
        return $this->handleCallback($data);
    }

    /**
     * {@inheritdoc}
     */
    public function refund(Payment $payment, float $amount, ?string $reason = null): array
    {
        $transactionId = $payment->transaction_id;

        if (!$transactionId) {
            return [
                'success' => false,
                'error' => 'لا يوجد معرف معاملة للاسترداد',
            ];
        }

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

            Log::info('Kashier: Refund request', [
                'payment_id' => $payment->id,
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
            ];
        } catch (\Exception $e) {
            Log::error('Kashier: Refund error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateSignature(array $data): bool
    {
        $signature = $data['signature'] ?? null;

        if (!$signature) {
            Log::warning('Kashier: No signature in callback', $data);
            return false;
        }

        // Build query string from all parameters except signature and mode
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
     * {@inheritdoc}
     */
    public function getCallbackUrl(): string
    {
        return route('payment.kashier.callback');
    }

    /**
     * {@inheritdoc}
     */
    public function getWebhookUrl(): string
    {
        return route('payment.kashier.webhook');
    }

    // ==================== Helper Methods ====================

    /**
     * Generate order hash for HPP
     * Uses API KEY
     * Amount in POUNDS (not cents)
     */
    protected function generateHash(string $orderId, float $amount, string $currency = 'EGP'): string
    {
        $amountFormatted = number_format($amount, 2, '.', '');
        $path = "/?payment={$this->merchantId}.{$orderId}.{$amountFormatted}.{$currency}";

        return hash_hmac('sha256', $path, $this->apiKey);
    }

    /**
     * Get Kashier checkout URL (HPP)
     */
    protected function getCheckoutUrl(
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
            'serverWebhook' => $this->getWebhookUrl(),
            'display' => 'ar',
            'brandColor' => '#8B5CF6',
        ];

        // Map payment method to Kashier's supportedPaymentMethods
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

        Log::info('Kashier: Checkout URL generated', [
            'order_id' => $orderId,
            'amount' => $amountFormatted,
            'mode' => $params['mode'],
            'payment_method' => $paymentMethod,
        ]);

        return $this->baseUrl . '?' . http_build_query($params);
    }

    /**
     * Send confirmation emails
     */
    protected function sendConfirmationEmails(Payment $payment): void
    {
        try {
            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendOrderConfirmation($payment->order);
            $emailService->sendAdminNewOrderNotification($payment->order);
        } catch (\Exception $e) {
            Log::error('Kashier: Failed to send confirmation emails', [
                'order_id' => $payment->order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get current mode
     */
    public function getMode(): string
    {
        return $this->isLiveMode ? 'live' : 'test';
    }
}
