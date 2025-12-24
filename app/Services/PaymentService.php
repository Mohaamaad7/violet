<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected KashierService $kashier
    ) {
    }

    /**
     * Initiate payment for an order
     */
    public function initiatePayment(Order $order, string $paymentMethod): array
    {
        return DB::transaction(function () use ($order, $paymentMethod) {
            // Check if method is enabled
            if (!PaymentSetting::isMethodEnabled($paymentMethod)) {
                return [
                    'success' => false,
                    'error' => 'طريقة الدفع غير متاحة',
                ];
            }

            // Check if Kashier is configured
            if (!$this->kashier->isConfigured()) {
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
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'gateway' => 'kashier',
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
            $checkoutUrl = $this->kashier->getCheckoutUrl(
                orderId: $payment->reference,
                amount: (float) $order->total,
                currency: 'EGP',
                callbackUrl: route('payment.callback'),
                customerData: $customerData,
                metadata: [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
                paymentMethod: $paymentMethod
            );

            // Update payment with gateway reference
            $payment->update([
                'gateway_order_id' => $payment->reference,
            ]);

            Log::channel('payments')->info('Payment initiated', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'amount' => $order->total,
                'method' => $paymentMethod,
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'redirect_url' => $checkoutUrl,
            ];
        });
    }

    /**
     * Handle callback from Kashier
     */
    public function handleCallback(array $data): array
    {
        // Validate signature first
        if (!$this->kashier->validateSignature($data)) {
            Log::warning('Invalid callback signature', $data);
            return [
                'success' => false,
                'error' => 'Invalid signature',
            ];
        }

        // Find payment by reference
        $orderId = $data['orderId'] ?? $data['merchantOrderId'] ?? null;
        $payment = Payment::where('reference', $orderId)
            ->orWhere('gateway_order_id', $orderId)
            ->first();

        if (!$payment) {
            Log::error('Payment not found for callback', ['order_id' => $orderId]);
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }

        // Idempotency check
        if ($payment->status === 'completed') {
            Log::info('Payment already completed (idempotency)', ['payment_id' => $payment->id]);
            return [
                'success' => true,
                'payment' => $payment,
                'already_processed' => true,
            ];
        }

        $paymentStatus = $data['paymentStatus'] ?? '';
        $transactionId = $data['transactionId'] ?? $data['cardDataToken'] ?? null;

        if (strtoupper($paymentStatus) === 'SUCCESS') {
            // Mark as completed
            $payment->markAsCompleted($transactionId, $data);

            // Update order status and payment info
            $payment->order->update([
                'status' => \App\Enums\OrderStatus::PENDING, // Ready for processing
                'payment_status' => 'paid',
                'payment_method' => $payment->payment_method,
                'payment_transaction_id' => $transactionId,
                'paid_at' => now(),
            ]);

            // Send confirmation emails now that payment is successful
            try {
                $emailService = app(\App\Services\EmailService::class);
                $emailService->sendOrderConfirmation($payment->order);
                $emailService->sendAdminNewOrderNotification($payment->order);
            } catch (\Exception $e) {
                // Log but don't fail
                Log::error('Failed to send payment confirmation emails', [
                    'order_id' => $payment->order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::channel('payments')->info('Payment completed', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => true,
                'payment' => $payment->fresh(),
                'status' => 'completed',
            ];
        } else {
            // Mark as failed
            $failReason = $data['failureReason'] ?? $data['error'] ?? 'Payment declined';
            $failCode = $data['errorCode'] ?? null;
            $payment->markAsFailed($failReason, $failCode, $data);

            Log::channel('payments')->warning('Payment failed', [
                'payment_id' => $payment->id,
                'reason' => $failReason,
            ]);

            return [
                'success' => false,
                'payment' => $payment->fresh(),
                'status' => 'failed',
                'error' => $failReason,
            ];
        }
    }

    /**
     * Get enabled payment methods with details
     */
    public function getEnabledMethods(): array
    {
        $allMethods = [
            'card' => [
                'name' => 'بطاقة ائتمانية',
                'name_en' => 'Credit Card',
                'icon' => 'credit-card',
                'description' => 'Visa, Mastercard, Meeza',
            ],
            'vodafone_cash' => [
                'name' => 'فودافون كاش',
                'name_en' => 'Vodafone Cash',
                'icon' => 'vodafone',
                'description' => 'ادفع من محفظة فودافون',
            ],
            'orange_money' => [
                'name' => 'أورانج موني',
                'name_en' => 'Orange Money',
                'icon' => 'orange',
                'description' => 'ادفع من محفظة أورانج',
            ],
            'etisalat_cash' => [
                'name' => 'اتصالات كاش',
                'name_en' => 'Etisalat Cash',
                'icon' => 'etisalat',
                'description' => 'ادفع من محفظة اتصالات',
            ],
            'meeza' => [
                'name' => 'ميزة',
                'name_en' => 'Meeza',
                'icon' => 'meeza',
                'description' => 'بطاقة ميزة الوطنية',
            ],
            'valu' => [
                'name' => 'ڤاليو',
                'name_en' => 'ValU',
                'icon' => 'valu',
                'description' => 'اشتري الآن وادفع لاحقاً',
            ],
        ];

        $enabled = [];

        foreach ($allMethods as $code => $details) {
            if (PaymentSetting::isMethodEnabled($code)) {
                $enabled[$code] = $details;
            }
        }

        return $enabled;
    }

    /**
     * Cancel expired payment
     */
    public function cancelExpiredPayment(Payment $payment): bool
    {
        if (!$payment->isExpired()) {
            return false;
        }

        $payment->update([
            'status' => 'expired',
        ]);

        Log::channel('payments')->info('Payment expired', [
            'payment_id' => $payment->id,
        ]);

        return true;
    }
}
