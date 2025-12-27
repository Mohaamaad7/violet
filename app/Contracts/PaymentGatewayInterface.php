<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Payment;

/**
 * Payment Gateway Interface
 * 
 * العقد المشترك لكل بوابات الدفع.
 * كل بوابة (Kashier, Paymob, etc.) يجب أن تطبق هذا الـ Interface.
 */
interface PaymentGatewayInterface
{
    /**
     * الاسم التقني للبوابة (kashier, paymob)
     */
    public function getName(): string;

    /**
     * الاسم المعروض للمستخدم
     */
    public function getDisplayName(): string;

    /**
     * هل البوابة مُعدّة وجاهزة للاستخدام؟
     */
    public function isConfigured(): bool;

    /**
     * اختبار الاتصال بالبوابة
     * 
     * @return array{success: bool, message: string, mode?: string}
     */
    public function testConnection(): array;

    /**
     * طرق الدفع المدعومة من هذه البوابة
     * 
     * @return array<string, array{name: string, name_en: string, icon: string, description?: string}>
     */
    public function getSupportedMethods(): array;

    /**
     * بدء عملية الدفع
     * 
     * @param Order $order الطلب المراد دفعه
     * @param string $method طريقة الدفع (card, wallet, kiosk, etc.)
     * @return array{success: bool, payment?: Payment, redirect_url?: string, error?: string}
     */
    public function initiatePayment(Order $order, string $method): array;

    /**
     * معالجة الـ Callback (redirect من البوابة بعد الدفع)
     * 
     * @param array $data البيانات المرسلة من البوابة
     * @return array{success: bool, payment?: Payment, order?: Order, error?: string, already_processed?: bool}
     */
    public function handleCallback(array $data): array;

    /**
     * معالجة الـ Webhook (server-to-server notification)
     * 
     * @param array $data البيانات المرسلة من البوابة
     * @return array{success: bool, message?: string}
     */
    public function handleWebhook(array $data): array;

    /**
     * استرداد مبلغ (Refund)
     * 
     * @param Payment $payment سجل الدفع
     * @param float $amount المبلغ المراد استرداده
     * @param string|null $reason سبب الاسترداد
     * @return array{success: bool, data?: array, error?: string}
     */
    public function refund(Payment $payment, float $amount, ?string $reason = null): array;

    /**
     * التحقق من صحة التوقيع (Signature/HMAC validation)
     * 
     * @param array $data البيانات المرسلة من البوابة
     * @return bool
     */
    public function validateSignature(array $data): bool;

    /**
     * جلب Callback URL لهذه البوابة
     * الـ URL الذي سيتم توجيه العميل إليه بعد الدفع
     */
    public function getCallbackUrl(): string;

    /**
     * جلب Webhook URL لهذه البوابة
     * الـ URL الذي ستستدعيه البوابة server-to-server
     */
    public function getWebhookUrl(): string;
}
