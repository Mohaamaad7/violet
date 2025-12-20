<?php

namespace App\Enums;

enum VarianceReasonType: string
{
    // عجز (shortage) - difference < 0
    case COMPANY_LOSS = 'company_loss';              // خسارة الشركة (تلف/انتهاء صلاحية)
    case EMPLOYEE_LIABILITY = 'employee_liability';  // مسؤولية موظف (عجز غير مبرر)
    case MEASUREMENT_ERROR = 'measurement_error';    // خطأ قياس

    // زيادة (excess) - difference > 0
    case COMPANY_GAIN = 'company_gain';              // مكسب للشركة
    case PREVIOUS_COUNT_ERROR = 'previous_count_error'; // خطأ جرد سابق

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match ($this) {
            self::COMPANY_LOSS => __('inventory.company_loss'),
            self::EMPLOYEE_LIABILITY => __('inventory.employee_liability'),
            self::MEASUREMENT_ERROR => __('inventory.measurement_error'),
            self::COMPANY_GAIN => __('inventory.company_gain'),
            self::PREVIOUS_COUNT_ERROR => __('inventory.previous_count_error'),
        };
    }

    /**
     * Get color for badges
     */
    public function color(): string
    {
        return match ($this) {
            self::COMPANY_LOSS => 'warning',
            self::EMPLOYEE_LIABILITY => 'danger',
            self::MEASUREMENT_ERROR => 'gray',
            self::COMPANY_GAIN => 'success',
            self::PREVIOUS_COUNT_ERROR => 'info',
        };
    }

    /**
     * Check if this reason requires a responsible employee
     */
    public function requiresResponsible(): bool
    {
        return $this === self::EMPLOYEE_LIABILITY;
    }

    /**
     * Check if this is a shortage reason
     */
    public function isShortage(): bool
    {
        return in_array($this, [
            self::COMPANY_LOSS,
            self::EMPLOYEE_LIABILITY,
            self::MEASUREMENT_ERROR,
        ]);
    }

    /**
     * Check if this is an excess reason
     */
    public function isExcess(): bool
    {
        return in_array($this, [
            self::COMPANY_GAIN,
            self::PREVIOUS_COUNT_ERROR,
        ]);
    }

    /**
     * Get options for shortage (difference < 0)
     */
    public static function shortageOptions(): array
    {
        return [
            self::COMPANY_LOSS->value => __('inventory.company_loss'),
            self::EMPLOYEE_LIABILITY->value => __('inventory.employee_liability'),
            self::MEASUREMENT_ERROR->value => __('inventory.measurement_error'),
        ];
    }

    /**
     * Get options for excess (difference > 0)
     */
    public static function excessOptions(): array
    {
        return [
            self::COMPANY_GAIN->value => __('inventory.company_gain'),
            self::PREVIOUS_COUNT_ERROR->value => __('inventory.previous_count_error'),
        ];
    }

    /**
     * Get all options
     */
    public static function options(): array
    {
        return array_merge(self::shortageOptions(), self::excessOptions());
    }
}
