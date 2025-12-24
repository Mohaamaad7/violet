<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PENDING = 0;
    case PENDING_PAYMENT = 6; // Awaiting online payment
    case PROCESSING = 1;
    case SHIPPED = 2;
    case DELIVERED = 3;
    case CANCELLED = 4;
    case REJECTED = 5;

    /**
     * Get the label for the enum value
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::PENDING_PAYMENT => 'في انتظار الدفع',
            self::PROCESSING => 'قيد التجهيز',
            self::SHIPPED => 'تم الشحن',
            self::DELIVERED => 'تم التسليم',
            self::CANCELLED => 'ملغي',
            self::REJECTED => 'مرفوض',
        };
    }

    /**
     * Get the color for the enum value (for badges)
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PENDING_PAYMENT => 'info',
            self::PROCESSING => 'info',
            self::SHIPPED => 'primary',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::REJECTED => 'danger',
        };
    }

    /**
     * Get the icon for the enum value
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::PENDING_PAYMENT => 'heroicon-o-credit-card',
            self::PROCESSING => 'heroicon-o-cog',
            self::SHIPPED => 'heroicon-o-truck',
            self::DELIVERED => 'heroicon-o-check-circle',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::REJECTED => 'heroicon-o-x-circle',
        };
    }

    /**
     * Get all labels as array
     */
    public static function labels(): array
    {
        return array_column(
            array_map(fn($case) => [$case->value, $case->label()], self::cases()),
            1,
            0
        );
    }

    /**
     * Get value from string (for backward compatibility)
     */
    public static function fromString(string $status): self
    {
        return match (strtolower($status)) {
            'pending' => self::PENDING,
            'pending_payment' => self::PENDING_PAYMENT,
            'processing' => self::PROCESSING,
            'shipped' => self::SHIPPED,
            'delivered' => self::DELIVERED,
            'cancelled' => self::CANCELLED,
            'rejected' => self::REJECTED,
            default => throw new \ValueError("Invalid order status: {$status}"),
        };
    }

    /**
     * Convert to string (for backward compatibility)
     */
    public function toString(): string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::PENDING_PAYMENT => 'pending_payment',
            self::PROCESSING => 'processing',
            self::SHIPPED => 'shipped',
            self::DELIVERED => 'delivered',
            self::CANCELLED => 'cancelled',
            self::REJECTED => 'rejected',
        };
    }
}
