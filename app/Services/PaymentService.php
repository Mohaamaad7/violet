<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Log;

/**
 * Payment Service
 * 
 * الخدمة الرئيسية للدفع - تستخدم Gateway Manager لتوجيه الطلبات للبوابة النشطة.
 */
class PaymentService
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager
    ) {
    }

    /**
     * Initiate payment for an order
     * 
     * يتم توجيه الطلب للبوابة النشطة تلقائياً.
     */
    public function initiatePayment(Order $order, string $paymentMethod): array
    {
        Log::error('PaymentService::initiatePayment called', [
            'order_id' => $order->id,
            'method' => $paymentMethod,
        ]);

        $gateway = $this->gatewayManager->getActiveGateway();

        Log::error('PaymentService: Active gateway retrieved', [
            'gateway_name' => $gateway->getName(),
            'is_configured' => $gateway->isConfigured(),
        ]);

        // Check if gateway is configured
        if (!$gateway->isConfigured()) {
            Log::warning('Payment gateway not configured', [
                'gateway' => $gateway->getName(),
                'order_id' => $order->id,
            ]);
            return [
                'success' => false,
                'error' => 'بوابة الدفع غير مُعدّة بشكل صحيح',
            ];
        }

        Log::error('PaymentService: Delegating to gateway', ['gateway' => $gateway->getName()]);

        // Delegate to the active gateway
        return $gateway->initiatePayment($order, $paymentMethod);
    }

    /**
     * Handle callback from a specific gateway
     * 
     * @param string $gatewayName اسم البوابة (kashier, paymob)
     * @param array $data البيانات المرسلة
     */
    public function handleCallback(string $gatewayName, array $data): array
    {
        try {
            $gateway = $this->gatewayManager->getGateway($gatewayName);
            return $gateway->handleCallback($data);
        } catch (\InvalidArgumentException $e) {
            Log::error('Unknown gateway for callback', [
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error' => 'بوابة دفع غير معروفة',
            ];
        }
    }

    /**
     * Handle webhook from a specific gateway
     * 
     * @param string $gatewayName اسم البوابة (kashier, paymob)
     * @param array $data البيانات المرسلة
     */
    public function handleWebhook(string $gatewayName, array $data): array
    {
        try {
            $gateway = $this->gatewayManager->getGateway($gatewayName);
            return $gateway->handleWebhook($data);
        } catch (\InvalidArgumentException $e) {
            Log::error('Unknown gateway for webhook', [
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error' => 'بوابة دفع غير معروفة',
            ];
        }
    }

    /**
     * Get enabled payment methods from the active gateway
     */
    public function getEnabledMethods(): array
    {
        try {
            $gateway = $this->gatewayManager->getActiveGateway();

            if (!$gateway->isConfigured()) {
                return [];
            }

            return $gateway->getSupportedMethods();
        } catch (\Exception $e) {
            Log::error('Failed to get payment methods', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get the active gateway instance
     */
    public function getActiveGateway(): PaymentGatewayInterface
    {
        return $this->gatewayManager->getActiveGateway();
    }

    /**
     * Get the active gateway name
     */
    public function getActiveGatewayName(): string
    {
        return $this->gatewayManager->getActiveGatewayName();
    }

    /**
     * Check if the active gateway is configured
     */
    public function isActiveGatewayConfigured(): bool
    {
        return $this->gatewayManager->isActiveGatewayConfigured();
    }

    /**
     * Process refund for a payment
     */
    public function refund(Payment $payment, float $amount, ?string $reason = null): array
    {
        $gatewayName = $payment->gateway;

        if (!$gatewayName) {
            return [
                'success' => false,
                'error' => 'لا توجد بوابة دفع مسجلة لهذه العملية',
            ];
        }

        try {
            $gateway = $this->gatewayManager->getGateway($gatewayName);
            return $gateway->refund($payment, $amount, $reason);
        } catch (\InvalidArgumentException $e) {
            Log::error('Cannot refund: unknown gateway', [
                'payment_id' => $payment->id,
                'gateway' => $gatewayName,
            ]);
            return [
                'success' => false,
                'error' => 'بوابة الدفع غير موجودة للاسترداد',
            ];
        }
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

        Log::info('Payment expired', [
            'payment_id' => $payment->id,
        ]);

        return true;
    }

    /**
     * Get available gateways list
     */
    public function getAvailableGateways(): array
    {
        return $this->gatewayManager->getAvailableGateways();
    }

    /**
     * Get available gateways with configuration status
     */
    public function getAvailableGatewaysWithStatus(): array
    {
        return $this->gatewayManager->getAvailableGatewaysWithStatus();
    }

    /**
     * Test connection for a specific gateway
     */
    public function testGatewayConnection(string $gatewayName): array
    {
        try {
            $gateway = $this->gatewayManager->getGateway($gatewayName);
            return $gateway->testConnection();
        } catch (\InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => 'بوابة دفع غير معروفة',
            ];
        }
    }
}
