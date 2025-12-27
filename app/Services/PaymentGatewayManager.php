<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentSetting;
use App\Services\Gateways\KashierGateway;
use App\Services\Gateways\PaymobGateway;
use InvalidArgumentException;

/**
 * Payment Gateway Manager
 * 
 * مدير بوابات الدفع - المسؤول عن:
 * - تحديد البوابة النشطة
 * - جلب بوابة محددة بالاسم
 * - قائمة البوابات المتاحة
 */
class PaymentGatewayManager
{
    /**
     * البوابات المسجلة
     * @var array<string, class-string<PaymentGatewayInterface>>
     */
    protected array $gateways = [];

    public function __construct()
    {
        // تسجيل البوابات المتاحة
        $this->gateways = [
            'kashier' => KashierGateway::class,
            'paymob' => PaymobGateway::class,
        ];
    }

    /**
     * جلب البوابة النشطة حالياً
     */
    public function getActiveGateway(): PaymentGatewayInterface
    {
        $activeGateway = $this->getActiveGatewayName();
        return $this->getGateway($activeGateway);
    }

    /**
     * جلب اسم البوابة النشطة
     */
    public function getActiveGatewayName(): string
    {
        return PaymentSetting::get('active_gateway', 'kashier');
    }

    /**
     * جلب بوابة محددة بالاسم
     * 
     * @throws InvalidArgumentException إذا كانت البوابة غير موجودة
     */
    public function getGateway(string $name): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new InvalidArgumentException("بوابة الدفع '{$name}' غير موجودة");
        }

        return app($this->gateways[$name]);
    }

    /**
     * قائمة البوابات المتاحة للعرض
     * 
     * @return array<string, string>
     */
    public function getAvailableGateways(): array
    {
        return [
            'kashier' => 'Kashier',
            'paymob' => 'Paymob (Accept)',
        ];
    }

    /**
     * قائمة البوابات المتاحة مع تفاصيل
     * 
     * @return array<string, array{name: string, configured: bool}>
     */
    public function getAvailableGatewaysWithStatus(): array
    {
        $result = [];

        foreach ($this->gateways as $name => $class) {
            try {
                $gateway = $this->getGateway($name);
                $result[$name] = [
                    'name' => $gateway->getDisplayName(),
                    'configured' => $gateway->isConfigured(),
                ];
            } catch (\Exception $e) {
                $result[$name] = [
                    'name' => ucfirst($name),
                    'configured' => false,
                ];
            }
        }

        return $result;
    }

    /**
     * هل البوابة النشطة مُعدّة وجاهزة؟
     */
    public function isActiveGatewayConfigured(): bool
    {
        try {
            return $this->getActiveGateway()->isConfigured();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * هل البوابة موجودة؟
     */
    public function hasGateway(string $name): bool
    {
        return isset($this->gateways[$name]);
    }

    /**
     * تسجيل بوابة جديدة (للتوسع المستقبلي)
     * 
     * @param string $name اسم البوابة
     * @param class-string<PaymentGatewayInterface> $class الكلاس
     */
    public function registerGateway(string $name, string $class): void
    {
        $this->gateways[$name] = $class;
    }
}
