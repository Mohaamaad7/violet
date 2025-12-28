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
 * Paymob Payment Gateway
 * 
 * بوابة باي موب للدفع الإلكتروني.
 * تدعم: البطاقات (Visa/MC/Meeza)، المحافظ الإلكترونية، فوري/Kiosk.
 * 
 * ⚠️ ملاحظة هامة: Paymob تتعامل بالقروش (15000) وليس بالجنيه (150.00)
 * يجب ضرب المبلغ في 100 قبل الإرسال.
 */
class PaymobGateway implements PaymentGatewayInterface
{
    protected string $apiKey;
    protected string $secretKey;
    protected string $publicKey;
    protected string $hmacSecret;
    protected string $integrationIdCard;
    protected string $integrationIdWallet;
    protected string $integrationIdKiosk;
    protected string $baseUrl = 'https://accept.paymob.com';

    public function __construct()
    {
        $config = PaymentSetting::getPaymobConfig();

        $this->apiKey = $config['api_key'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->publicKey = $config['public_key'] ?? '';
        $this->hmacSecret = $config['hmac_secret'] ?? '';
        $this->integrationIdCard = $config['integration_id_card'] ?? '';
        $this->integrationIdWallet = $config['integration_id_wallet'] ?? '';
        $this->integrationIdKiosk = $config['integration_id_kiosk'] ?? '';
    }

    // ==================== Interface Implementation ====================

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'paymob';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName(): string
    {
        return 'Paymob (Accept)';
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->secretKey) && !empty($this->publicKey);
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'بيانات Paymob غير مكتملة (API Key و Secret Key و Public Key مطلوبين)',
            ];
        }

        // Test by making an authenticated API call to verify credentials
        try {
            // Use the /api/auth/tokens endpoint to verify API key
            $response = Http::timeout(10)->post($this->baseUrl . '/api/auth/tokens', [
                'api_key' => $this->apiKey,
            ]);

            if ($response->successful() && isset($response->json()['token'])) {
                // Credentials are valid
                return [
                    'success' => true,
                    'message' => 'الاتصال ناجح - المفاتيح صحيحة',
                    'has_card_integration' => !empty($this->integrationIdCard),
                    'has_wallet_integration' => !empty($this->integrationIdWallet),
                    'has_kiosk_integration' => !empty($this->integrationIdKiosk),
                ];
            }

            // Check for specific error messages
            $errorMessage = $response->json()['message'] ?? $response->json()['detail'] ?? 'بيانات غير صحيحة';

            return [
                'success' => false,
                'message' => 'فشل التحقق: ' . $errorMessage,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'خطأ في الاتصال: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedMethods(): array
    {
        $methods = [];

        // Card payments (Visa/Mastercard/Meeza)
        if (!empty($this->integrationIdCard) && PaymentSetting::isMethodEnabled('card')) {
            $methods['card'] = [
                'name' => 'بطاقة ائتمانية',
                'name_en' => 'Credit Card',
                'icon' => 'credit-card',
                'description' => 'Visa, Mastercard, Meeza',
            ];
        }

        // Wallet payments
        if (!empty($this->integrationIdWallet)) {
            if (PaymentSetting::isMethodEnabled('vodafone_cash')) {
                $methods['wallet'] = [
                    'name' => 'محفظة إلكترونية',
                    'name_en' => 'Mobile Wallet',
                    'icon' => 'wallet',
                    'description' => 'فودافون كاش، أورانج، اتصالات',
                ];
            }
        }

        // Kiosk payments (Fawry/Aman)
        if (!empty($this->integrationIdKiosk) && PaymentSetting::isMethodEnabled('kiosk')) {
            $methods['kiosk'] = [
                'name' => 'فوري',
                'name_en' => 'Fawry/Kiosk',
                'icon' => 'kiosk',
                'description' => 'ادفع نقداً في أي فرع فوري أو أمان',
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
            // Get Integration ID based on method
            $integrationId = $this->getIntegrationId($method);

            if (!$integrationId) {
                return [
                    'success' => false,
                    'error' => 'طريقة الدفع غير مُعدّة',
                ];
            }

            // Check if configured
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'بوابة Paymob غير مُعدّة',
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

            try {
                // Create Intention via Paymob API
                $intentionResult = $this->createIntention($order, $payment, $integrationId);

                if (!$intentionResult['success']) {
                    $payment->markAsFailed($intentionResult['error'], null, $intentionResult);
                    return $intentionResult;
                }

                // Update payment with gateway references
                $payment->update([
                    'gateway_order_id' => $intentionResult['intention_id'] ?? null,
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'client_secret' => $intentionResult['client_secret'],
                        'intention_id' => $intentionResult['intention_id'],
                    ]),
                ]);

                // Build checkout URL
                $checkoutUrl = $this->buildCheckoutUrl($intentionResult['client_secret']);

                Log::info('Paymob: Payment initiated', [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'amount' => $order->total,
                    'amount_cents' => $this->toCents($order->total),
                    'method' => $method,
                    'integration_id' => $integrationId,
                ]);

                return [
                    'success' => true,
                    'payment' => $payment,
                    'redirect_url' => $checkoutUrl,
                ];
            } catch (\Exception $e) {
                Log::error('Paymob: Failed to initiate payment', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);

                $payment->markAsFailed($e->getMessage());

                return [
                    'success' => false,
                    'error' => 'حدث خطأ أثناء إنشاء طلب الدفع',
                ];
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function handleCallback(array $data): array
    {
        // Log ALL received data for debugging
        Log::info('Paymob: Raw callback data', [
            'all_data' => $data,
            'keys' => array_keys($data),
            'data_types' => array_map('gettype', $data),
        ]);

        // Validate HMAC signature (skip if no hmac - Unified Checkout may not send it in redirect)
        if (isset($data['hmac']) && !$this->validateSignature($data)) {
            Log::warning('Paymob: Invalid callback HMAC', $data);
            return [
                'success' => false,
                'error' => 'Invalid HMAC signature',
            ];
        }

        // Extract payment info from callback
        // Paymob Unified Checkout sends data in query parameters

        // Try to extract from payment_key_claims (new Unified Checkout format)
        $paymentKeyClaims = null;
        if (isset($data['payment_key_claims']) && is_string($data['payment_key_claims'])) {
            $paymentKeyClaims = json_decode($data['payment_key_claims'], true);
            Log::info('Paymob: Decoded payment_key_claims', ['claims' => $paymentKeyClaims]);
        }

        // Extract values with fallbacks
        $success = $data['success'] ?? (isset($data['txn_response_code']) && $data['txn_response_code'] == '200') ?? false;
        $transactionId = $data['id'] ?? $data['transaction_id'] ?? $data['txn_id'] ?? null;
        $orderId = $data['order'] ?? $data['order_id'] ?? ($paymentKeyClaims['order_id'] ?? null);
        $merchantOrderId = $data['merchant_order_id']
            ?? $data['special_reference']
            ?? ($paymentKeyClaims['merchant_order_id'] ?? null);
        $intentionId = $data['intention'] ?? $data['payment_intention'] ?? null;
        $amountCents = $data['amount_cents'] ?? ($paymentKeyClaims['amount_cents'] ?? null);

        Log::info('Paymob: Parsed callback values', [
            'success' => $success,
            'transactionId' => $transactionId,
            'orderId' => $orderId,
            'merchantOrderId' => $merchantOrderId,
            'intentionId' => $intentionId,
            'amountCents' => $amountCents,
        ]);

        // Find payment by multiple criteria with better query logic
        $payment = null;

        // Try 1: Find by merchant_order_id (payment reference)
        if ($merchantOrderId) {
            $payment = Payment::where('reference', $merchantOrderId)->first();
            if ($payment) {
                Log::info('Paymob: Found payment by reference', ['payment_id' => $payment->id]);
            }
        }

        // Try 2: Find by order_id in gateway_order_id
        if (!$payment && $orderId) {
            $payment = Payment::where('gateway_order_id', $orderId)->first();
            if ($payment) {
                Log::info('Paymob: Found payment by gateway_order_id', ['payment_id' => $payment->id]);
            }
        }

        // Try 3: Find by intention_id in metadata
        if (!$payment && $intentionId) {
            $payment = Payment::whereJsonContains('metadata->intention_id', $intentionId)->first();
            if ($payment) {
                Log::info('Paymob: Found payment by intention_id', ['payment_id' => $payment->id]);
            }
        }

        // Try 4: Find by transaction_id
        if (!$payment && $transactionId) {
            $payment = Payment::where('transaction_id', $transactionId)
                ->orWhere('gateway_transaction_id', $transactionId)
                ->first();
            if ($payment) {
                Log::info('Paymob: Found payment by transaction_id', ['payment_id' => $payment->id]);
            }
        }

        // Try 5: Last resort - find recent pending payment for same amount
        if (!$payment && $amountCents) {
            $amountEgp = $amountCents / 100;
            $payment = Payment::where('gateway', 'paymob')
                ->where('status', 'pending')
                ->where('amount', $amountEgp)
                ->where('created_at', '>', now()->subHours(2))
                ->orderBy('created_at', 'desc')
                ->first();
            if ($payment) {
                Log::warning('Paymob: Found payment by amount fallback', [
                    'payment_id' => $payment->id,
                    'amount' => $amountEgp,
                ]);
            }
        }

        if (!$payment) {
            Log::error('Paymob: Payment not found for callback - ALL ATTEMPTS FAILED', [
                'merchant_order_id' => $merchantOrderId,
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'intention_id' => $intentionId,
                'amount_cents' => $amountCents,
                'all_data_keys' => array_keys($data),
                'sample_payments' => Payment::where('gateway', 'paymob')
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get(['id', 'reference', 'gateway_order_id', 'created_at'])
                    ->toArray(),
            ]);
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }

        // Idempotency check
        if ($payment->status === 'completed') {
            Log::info('Paymob: Payment already completed (idempotency)', ['payment_id' => $payment->id]);
            return [
                'success' => true,
                'payment' => $payment,
                'order' => $payment->order,
                'already_processed' => true,
            ];
        }

        // Check success status
        $isSuccess = filter_var($success, FILTER_VALIDATE_BOOLEAN);

        if ($isSuccess) {
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

            Log::info('Paymob: Payment completed', [
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
            $failReason = $data['data_message'] ?? $data['pending'] ?? 'Payment declined';
            $failCode = $data['txn_response_code'] ?? null;
            $payment->markAsFailed($failReason, $failCode, $data);

            Log::warning('Paymob: Payment failed', [
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
        // Paymob webhook sends data in 'obj' key
        $obj = $data['obj'] ?? $data;

        // Extract transaction data
        $transactionData = [
            'success' => $obj['success'] ?? false,
            'id' => $obj['id'] ?? null,
            'transaction_id' => $obj['id'] ?? null,
            'order_id' => $obj['order']['id'] ?? null,
            'merchant_order_id' => $obj['order']['merchant_order_id'] ?? null,
            'amount_cents' => $obj['amount_cents'] ?? null,
            'data_message' => $obj['data']['message'] ?? null,
            'txn_response_code' => $obj['data']['txn_response_code'] ?? null,
            'hmac' => $data['hmac'] ?? null,
        ];

        return $this->handleCallback($transactionData);
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

        try {
            // First, authenticate to get token
            $authResponse = Http::post($this->baseUrl . '/api/auth/tokens', [
                'api_key' => $this->secretKey,
            ]);

            if (!$authResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'فشل المصادقة مع Paymob',
                ];
            }

            $authToken = $authResponse->json()['token'];

            // Process refund
            $refundResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $authToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/acceptance/void_refund/refund', [
                        'transaction_id' => $transactionId,
                        'amount_cents' => $this->toCents($amount),
                    ]);

            Log::info('Paymob: Refund request', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'response' => $refundResponse->json(),
            ]);

            if ($refundResponse->successful()) {
                return [
                    'success' => true,
                    'data' => $refundResponse->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $refundResponse->json()['message'] ?? 'فشل الاسترداد',
            ];
        } catch (\Exception $e) {
            Log::error('Paymob: Refund error', [
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
     * 
     * Paymob HMAC validation
     * https://developers.paymob.com/egypt/hmac-calculation
     */
    public function validateSignature(array $data): bool
    {
        $hmac = $data['hmac'] ?? null;

        if (!$hmac) {
            // If no HMAC in data, try to calculate from transaction response
            // Some callbacks might not have HMAC
            Log::info('Paymob: No HMAC in callback, skipping validation');
            return true; // Or return false for strict validation
        }

        if (empty($this->hmacSecret)) {
            Log::warning('Paymob: HMAC secret not configured');
            return false;
        }

        // Paymob HMAC calculation
        // The HMAC is calculated from specific fields in a specific order
        $hmacFields = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order.id',
            'owner',
            'pending',
            'source_data.pan',
            'source_data.sub_type',
            'source_data.type',
            'success',
        ];

        $hmacString = '';
        foreach ($hmacFields as $field) {
            $value = $this->getNestedValue($data, $field);
            if ($value !== null) {
                $hmacString .= $value;
            }
        }

        $calculatedHmac = hash_hmac('sha512', $hmacString, $this->hmacSecret);

        $isValid = hash_equals(strtolower($calculatedHmac), strtolower($hmac));

        if (!$isValid) {
            Log::warning('Paymob: Invalid HMAC', [
                'received' => $hmac,
                'calculated' => $calculatedHmac,
            ]);
        }

        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallbackUrl(): string
    {
        // Use url() instead of route() to ensure correct domain
        return url('/payment/paymob/callback');
    }

    /**
     * {@inheritdoc}
     */
    public function getWebhookUrl(): string
    {
        return url('/payment/paymob/webhook');
    }

    // ==================== Helper Methods ====================

    /**
     * Convert amount to cents (Paymob requirement)
     * 
     * @param float $amount Amount in EGP (e.g., 150.00)
     * @return int Amount in cents (e.g., 15000)
     */
    protected function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Get Integration ID based on payment method
     */
    protected function getIntegrationId(string $method): ?string
    {
        return match ($method) {
            'card' => $this->integrationIdCard,
            'wallet' => $this->integrationIdWallet,
            'kiosk' => $this->integrationIdKiosk,
            default => null,
        };
    }

    /**
     * Create Payment Intention via Paymob API
     */
    protected function createIntention(Order $order, Payment $payment, string $integrationId): array
    {
        $amountCents = $this->toCents((float) $order->total);

        $payload = [
            'amount' => $amountCents,
            'currency' => 'EGP',
            'payment_methods' => [(int) $integrationId],
            'items' => $this->formatItems($order),
            'billing_data' => [
                'first_name' => $order->customer_name ?? 'Customer',
                'last_name' => '.',
                'email' => $order->customer_email ?? 'customer@example.com',
                'phone_number' => $order->customer_phone ?? '+201000000000',
                'apartment' => 'N/A',
                'floor' => 'N/A',
                'street' => $order->shipping_address ?? 'N/A',
                'building' => 'N/A',
                'city' => $order->shipping_city ?? 'Cairo',
                'country' => 'EG',
                'state' => $order->shipping_state ?? 'Cairo',
                'postal_code' => 'N/A',
            ],
            'customer' => [
                'first_name' => $order->customer_name ?? 'Customer',
                'last_name' => '.',
                'email' => $order->customer_email ?? 'customer@example.com',
            ],
            'special_reference' => $payment->reference,
            'redirection_url' => $this->getCallbackUrl(),
            'notification_url' => $this->getWebhookUrl(),
        ];

        Log::info('Paymob: Intention payload being sent', [
            'redirection_url' => $payload['redirection_url'],
            'notification_url' => $payload['notification_url'],
            'special_reference' => $payload['special_reference'],
            'amount' => $payload['amount'],
            'integration_id' => $integrationId,
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v1/intention/', $payload);

            Log::info('Paymob: Intention API response', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'client_secret' => $data['client_secret'] ?? null,
                    'intention_id' => $data['id'] ?? null,
                    'payment_keys' => $data['payment_keys'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'فشل إنشاء طلب الدفع',
                'details' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Paymob: Intention API error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build Unified Checkout URL
     */
    protected function buildCheckoutUrl(string $clientSecret): string
    {
        return $this->baseUrl . '/unifiedcheckout/?' . http_build_query([
            'publicKey' => $this->publicKey,
            'clientSecret' => $clientSecret,
        ]);
    }

    /**
     * Format order items for Paymob
     * 
     * ⚠️ Important: 
     * - Paymob doesn't accept HTML tags in item descriptions
     * - Sum of item amounts MUST equal the total amount exactly
     * - Using single item approach to avoid unmatched_item_prices error
     */
    protected function formatItems(Order $order): array
    {
        $orderTotalCents = $this->toCents((float) $order->total);

        // Build item names from order items
        $itemNames = [];
        foreach ($order->items as $item) {
            $name = $item->product_name ?? $item->product?->name ?? 'Product';
            $itemNames[] = mb_substr($name, 0, 30) . ' x' . $item->quantity;
        }

        $description = implode(', ', $itemNames);
        if (strlen($description) > 100) {
            $description = mb_substr($description, 0, 97) . '...';
        }

        // Use single item with exact total to avoid price mismatch issues
        return [
            [
                'name' => 'Order #' . $order->order_number,
                'amount' => $orderTotalCents,
                'description' => $description ?: 'Order items',
                'quantity' => 1,
            ]
        ];
    }

    /**
     * Get nested value from array using dot notation
     */
    protected function getNestedValue(array $data, string $key)
    {
        $keys = explode('.', $key);
        $value = $data;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }

        // Convert boolean to string representation
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
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
            Log::error('Paymob: Failed to send confirmation emails', [
                'order_id' => $payment->order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
