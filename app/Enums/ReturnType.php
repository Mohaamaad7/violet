<?php

namespace App\Enums;

enum ReturnType: int
{
    case REJECTION = 0;
    case RETURN_AFTER_DELIVERY = 1;

    /**
     * Get the label for the enum value
     */
    public function label(): string
    {
        return match($this) {
            self::REJECTION => 'رفض استلام',
            self::RETURN_AFTER_DELIVERY => 'استرجاع بعد التسليم',
        };
    }

    /**
     * Get the color for the enum value (for badges)
     */
    public function color(): string
    {
        return match($this) {
            self::REJECTION => 'danger',
            self::RETURN_AFTER_DELIVERY => 'warning',
        };
    }

    /**
     * Get the icon for the enum value
     */
    public function icon(): string
    {
        return match($this) {
            self::REJECTION => 'heroicon-o-x-circle',
            self::RETURN_AFTER_DELIVERY => 'heroicon-o-arrow-uturn-left',
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
    public static function fromString(string $type): self
    {
        return match(strtolower($type)) {
            'rejection' => self::REJECTION,
            'return_after_delivery', 'return after delivery' => self::RETURN_AFTER_DELIVERY,
            default => throw new \ValueError("Invalid return type: {$type}"),
        };
    }

    /**
     * Convert to string (for backward compatibility)
     */
    public function toString(): string
    {
        return match($this) {
            self::REJECTION => 'rejection',
            self::RETURN_AFTER_DELIVERY => 'return_after_delivery',
        };
    }
}
