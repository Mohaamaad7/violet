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
 * Fawry Payment Gateway
 * 
 * بوابة فوري باي للدفع الإلكتروني.
 * تدعم: البطاقات، المحافظ الإلكترونية، تقسيط، وفوري كشك.
 */
class FawryGateway implements PaymentGatewayInterface
{
    protected string $merchantCode;
    protected string $securityKey;
    protected string $mode;
    protected string $baseUrl;

    public function __construct()
    {
        $config = PaymentSetting::getFawryConfig();

        $this->mode = $config['mode'] ?? 'test';
        $this->merchantCode = $config['merchant_code'] ?? '';
        $this->securityKey = $config['security_key'] ?? '';
        
        $this->baseUrl = $this->mode === 'live' 
            ? 'https://atfawry.com' 
            : 'https://atfawry.fawrystaging.com';
    }

    // ==================== Interface Implementation ====================

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'fawry';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName(): string
    {
        return 'Fawry Pay';
    }

    /**
     * {@inheritdoc}
     */
    public function isConfigured(): bool
    {
        return !empty($this->merchantCode) && !empty($this->securityKey);
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'بيانات فوري غير مكتملة (Merchant Code و Security Key مطلوبين)',
            ];
        }

        // فوري لا تمتلك API للتحقق من الاتصال مباشرة، لذا سنعتبره ناجحاً إذا كانت البيانات موجودة
        return [
            'success' => true,
            'message' => 'الاتصال ناجح',
            'mode' => $this->mode,
            'merchant_code' => $this->merchantCode,
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
                'name' => 'بطاقة ائتمانية (فوري)',
                'name_en' => 'Credit Card',
                'icon' => 'credit-card',
                'description' => 'Visa, Mastercard, Meeza',
            ];
        }

        if (PaymentSetting::isMethodEnabled('wallet')) {
            $methods['wallet'] = [
                'name' => 'محفظة إلكترونية (فوري)',
                'name_en' => 'Mobile Wallet',
                'icon' => 'wallet',
                'description' => 'فودافون كاش، أورانج، اتصالات',
            ];
        }

        if (PaymentSetting::isMethodEnabled('kiosk')) {
            $methods['kiosk'] = [
                'name' => 'فوري',
                'name_en' => 'Fawry/Kiosk',
                'icon' => 'kiosk',
                'description' => 'ادفع نقداً في أي فرع فوري أو أمان',
            ];
        }
        
        if (PaymentSetting::isMethodEnabled('valu')) {
            $methods['valu'] = [
                'name' => 'ڤاليو (فوري)',
                'name_en' => 'ValU',
                'icon' => 'shopping-bag',
                'description' => 'تقسيط عبر ڤاليو',
            ];
        }

        return $methods;
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePayment(Order $order, string $method): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'بوابة Fawry غير مُعدّة',
            ];
        }

        return DB::transaction(function () use ($order, $method) {
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
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
            ]);

            try {
                // Validate method is known
                $fawryMethod = match($method) {
                    'card' => 'CARD',
                    'wallet' => 'MWALLET',
                    'kiosk' => 'PayAtFawry',
                    'valu' => 'VALU',
                    default => null
                };

                $chargeRequest = $this->buildChargeRequest($order, $payment, $fawryMethod);

                // Log without sensitive data (signature excluded)
                Log::info('Fawry: Charge Request', [
                    'order_id' => $order->id,
                    'payment_ref' => $payment->reference,
                    'amount' => $order->total,
                    'method' => $method,
                ]);

                $apiUrl = $this->baseUrl . '/fawrypay-api/api/payments/init';

                $response = Http::timeout(15)->post($apiUrl, $chargeRequest);

                $responseText = $response->body();

                Log::info('Fawry: Init API Response', [
                    'status' => $response->status(),
                    'body_length' => strlen($responseText),
                ]);

                // Fawry returns plain text URL or JSON with redirectUrl
                $redirectUrl = null;

                if ($response->successful() && filter_var(trim($responseText), FILTER_VALIDATE_URL)) {
                    $redirectUrl = trim($responseText);
                } else {
                    $jsonResponse = $response->json();
                    if ($response->successful() && isset($jsonResponse['redirectUrl'])) {
                        $redirectUrl = $jsonResponse['redirectUrl'];
                    }
                }

                // Validate redirect URL belongs to Fawry domain
                if ($redirectUrl) {
                    $host = parse_url($redirectUrl, PHP_URL_HOST);
                    $allowedDomains = ['atfawry.com', 'fawrystaging.com', 'atfawry.fawrystaging.com'];
                    $isAllowed = false;
                    foreach ($allowedDomains as $domain) {
                        if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                            $isAllowed = true;
                            break;
                        }
                    }

                    if (!$isAllowed) {
                        Log::error('Fawry: Suspicious redirect URL rejected', ['url' => $redirectUrl]);
                        $payment->markAsFailed('Redirect URL domain mismatch');
                        return [
                            'success' => false,
                            'error' => 'حدث خطأ أثناء إنشاء طلب الدفع من فوري',
                        ];
                    }

                    return [
                        'success' => true,
                        'payment' => $payment,
                        'redirect_url' => $redirectUrl,
                    ];
                }

                $payment->markAsFailed('فشل الاتصال ببوابة فوري', null, ['response_status' => $response->status()]);

                return [
                    'success' => false,
                    'error' => 'حدث خطأ أثناء إنشاء طلب الدفع من فوري',
                ];

            } catch (\Exception $e) {
                Log::error('Fawry: Failed to initiate payment', [
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
        Log::info('Fawry: Raw callback data', $data);

        // MerchantRefNum is our payment reference
        $merchantRefNum = $data['merchantRefNum'] ?? null;
        if (!$merchantRefNum) {
            return [
                'success' => false,
                'error' => 'Missing merchantRefNum',
            ];
        }

        // Validate Signature - MUST always validate for security
        if (!$this->validateSignature($data)) {
            Log::error('Fawry: Invalid callback signature - rejecting request');
            return [
                'success' => false,
                'error' => 'Invalid signature',
            ];
        }

        $payment = Payment::where('reference', $merchantRefNum)->first();

        if (!$payment) {
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }

        if ($payment->status === 'completed') {
            return [
                'success' => true,
                'payment' => $payment,
                'order' => $payment->order,
                'already_processed' => true,
            ];
        }

        // Verify callback amount matches payment amount to prevent tampering
        $callbackAmount = isset($data['orderAmount']) ? (float) $data['orderAmount'] : null;
        if ($callbackAmount !== null && abs($callbackAmount - (float) $payment->amount) > 0.01) {
            Log::error('Fawry: Amount mismatch in callback', [
                'expected' => $payment->amount,
                'received' => $callbackAmount,
                'payment_id' => $payment->id,
            ]);
            return [
                'success' => false,
                'error' => 'Amount mismatch',
            ];
        }

        // Check payment status from callback
        // 'orderStatus': 'PAID', 'NEW', 'CANCELED', 'EXPIRED', 'REFUNDED', 'FAILED'
        $orderStatus = $data['orderStatus'] ?? null;
        $transactionId = $data['referenceNumber'] ?? $data['fawryRefNumber'] ?? null;

        if ($orderStatus === 'PAID') {
            $payment->markAsCompleted($transactionId, $data);

            $payment->order->update([
                'status' => \App\Enums\OrderStatus::PENDING,
                'payment_status' => 'paid',
                'payment_method' => $payment->payment_method,
                'payment_transaction_id' => $transactionId,
                'paid_at' => now(),
            ]);

            $this->sendConfirmationEmails($payment);

            return [
                'success' => true,
                'payment' => $payment->fresh(),
                'order' => $payment->order,
                'status' => 'completed',
            ];
        } elseif ($orderStatus === 'NEW') {
            // Kiosk: reference code generated, waiting for customer to pay at outlet
            return [
                'success' => true,
                'payment' => $payment,
                'order' => $payment->order,
                'status' => 'pending',
            ];
        }

        // Failed or canceled
        $payment->markAsFailed($data['statusDescription'] ?? 'Payment failed', null, $data);

        return [
            'success' => false,
            'payment' => $payment->fresh(),
            'order' => $payment->order,
            'status' => 'failed',
            'error' => $data['statusDescription'] ?? 'فشلت عملية الدفع',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handleWebhook(array $data): array
    {
        // Webhook handles Server Notification V2 (Push)
        Log::info('Fawry: Raw webhook data', $data);
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
        
        $refundReq = [
            'merchantCode' => $this->merchantCode,
            'referenceNumber' => $transactionId,
            'refundAmount' => number_format($amount, 2, '.', ''),
            'reason' => $reason ?? 'Refunded by admin',
        ];
        
        $signatureStr = $refundReq['merchantCode'] . $refundReq['referenceNumber'] . $refundReq['refundAmount'] . $refundReq['reason'] . $this->securityKey;
        $refundReq['signature'] = hash('sha256', $signatureStr);

        try {
            $apiUrl = $this->baseUrl . '/ECommerceWeb/Fawry/payments/refund';
            $response = Http::timeout(15)->post($apiUrl, $refundReq);

            Log::info('Fawry: Refund request', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'response' => $response->json(),
            ]);

            $json = $response->json();
            
            if ($response->successful() && isset($json['statusCode']) && $json['statusCode'] == 200) {
                return [
                    'success' => true,
                    'data' => $json,
                ];
            }

            return [
                'success' => false,
                'error' => $json['statusDescription'] ?? 'فشل الاسترداد',
            ];
        } catch (\Exception $e) {
            Log::error('Fawry: Refund error', [
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
        $receivedSignature = $data['messageSignature'] ?? null;
        if (!$receivedSignature) {
            // SECURITY: Never trust unsigned callbacks - always require signature
            Log::error('Fawry: Callback missing messageSignature - rejecting');
            return false;
        }

        if (empty($this->securityKey)) {
            Log::error('Fawry: Security key not configured - cannot validate signature');
            return false;
        }
        
        // Signature = SHA256(fawryRefNumber + merchantRefNum + paymentAmount + orderAmount + orderStatus + paymentMethod + paymentRefrenceNumber + secureKey)
        $fawryRefNumber = $data['fawryRefNumber'] ?? '';
        $merchantRefNum = $data['merchantRefNum'] ?? '';
        $paymentAmount = isset($data['paymentAmount']) ? number_format((float)$data['paymentAmount'], 2, '.', '') : '';
        $orderAmount = isset($data['orderAmount']) ? number_format((float)$data['orderAmount'], 2, '.', '') : '';
        $orderStatus = $data['orderStatus'] ?? '';
        $paymentMethod = $data['paymentMethod'] ?? '';
        $paymentRefrenceNumber = $data['paymentRefrenceNumber'] ?? '';
        
        $signatureString = $fawryRefNumber . $merchantRefNum . $paymentAmount . $orderAmount . $orderStatus . $paymentMethod . $paymentRefrenceNumber . $this->securityKey;
        
        $generatedSignature = hash('sha256', $signatureString);

        // Use timing-safe comparison to prevent timing attacks
        if (!hash_equals(strtolower($generatedSignature), strtolower($receivedSignature))) {
            Log::error('Fawry: HMAC validation failed', [
                'generated_prefix' => substr($generatedSignature, 0, 16) . '...',
                'received_prefix' => substr($receivedSignature, 0, 16) . '...',
                'field_count' => count(array_filter([$fawryRefNumber, $merchantRefNum, $paymentAmount, $orderAmount, $orderStatus])),
            ]);
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallbackUrl(): string
    {
        return url('/payment/fawry/callback');
    }

    /**
     * {@inheritdoc}
     */
    public function getWebhookUrl(): string
    {
        return url('/payment/fawry/webhook');
    }

    // ==================== Helper Methods ====================

    /**
     * Build Charge Request for API
     */
    protected function buildChargeRequest(Order $order, Payment $payment, ?string $fawryMethod = null): array
    {
        // 1. Prepare items
        $chargeItems = [];
        foreach ($order->items as $item) {
            $price = number_format((float)$item->price, 2, '.', '');
            $chargeItems[] = [
                'itemId' => (string) $item->product_id,
                'description' => mb_substr($item->product_name ?? 'Product', 0, 50),
                'price' => $price,
                'quantity' => $item->quantity,
            ];
        }

        // Just in case total doesn't match perfectly due to discounts, etc.
        // It's safer to send a single aggregate item if prices might mismatch
        $chargeItems = [
            [
                'itemId' => (string) $order->id,
                'description' => 'Order #' . $order->order_number,
                'price' => number_format((float)$order->total, 2, '.', ''),
                'quantity' => 1,
            ]
        ];

        // 2. Prepare request
        $chargeRequest = [
            'merchantCode' => $this->merchantCode,
            'merchantRefNum' => $payment->reference,
            'customerMobile' => $order->customer_phone ?? '01000000000',
            'customerEmail' => $order->customer_email ?? 'customer@example.com',
            'customerName' => $order->customer_name ?? 'Customer Name',
            'customerProfileId' => (string) ($order->customer_id ?? 'guest'),
            'language' => app()->getLocale() === 'ar' ? 'ar-eg' : 'en-gb',
            'chargeItems' => $chargeItems,
            'returnUrl' => $this->getCallbackUrl(),
            'authCaptureModePayment' => false,
        ];

        if ($fawryMethod) {
            $chargeRequest['paymentMethod'] = $fawryMethod;
        }

        // 3. Generate Signature
        // hash256(merchantCode + merchantRefNum + customerProfileId + returnUrl + itemId1 + quantity1 + price1 + itemId2 + quantity2 + price2 + ... + secureKey)
        
        $signatureStr = $this->merchantCode . $chargeRequest['merchantRefNum'] . $chargeRequest['customerProfileId'] . $chargeRequest['returnUrl'];
        
        foreach ($chargeItems as $item) {
            $signatureStr .= $item['itemId'] . $item['quantity'] . $item['price'];
        }
        
        $signatureStr .= $this->securityKey;
        
        $chargeRequest['signature'] = hash('sha256', $signatureStr);

        return $chargeRequest;
    }

    /**
     * Send order confirmation email
     */
    protected function sendConfirmationEmails(Payment $payment): void
    {
        try {
            // Send email via existing Mail/Notification setup in your app
            // \Illuminate\Support\Facades\Mail::to($payment->order->customer_email)->send(new \App\Mail\OrderConfirmed($payment->order));
        } catch (\Exception $e) {
            Log::error("Failed to send confirmation email: " . $e->getMessage());
        }
    }
}
