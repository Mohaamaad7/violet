<?php

namespace App\Enums;

enum ReturnStatus: int
{
    case PENDING = 0;
    case APPROVED = 1;
    case REJECTED = 2;
    case COMPLETED = 3;

    /**
     * Get the label for the enum value
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'قيد الانتظار',
            self::APPROVED => 'موافق عليه',
            self::REJECTED => 'مرفوض',
            self::COMPLETED => 'مكتمل',
        };
    }

    /**
     * Get the color for the enum value (for badges)
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'info',
            self::REJECTED => 'danger',
            self::COMPLETED => 'success',
        };
    }

    /**
     * Get the icon for the enum value
     */
    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::APPROVED => 'heroicon-o-check',
            self::REJECTED => 'heroicon-o-x-circle',
            self::COMPLETED => 'heroicon-o-check-circle',
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
        return match(strtolower($status)) {
            'pending' => self::PENDING,
            'approved' => self::APPROVED,
            'rejected' => self::REJECTED,
            'completed' => self::COMPLETED,
            default => throw new \ValueError("Invalid return status: {$status}"),
        };
    }

    /**
     * Convert to string (for backward compatibility)
     */
    public function toString(): string
    {
        return match($this) {
            self::PENDING => 'pending',
            self::APPROVED => 'approved',
            self::REJECTED => 'rejected',
            self::COMPLETED => 'completed',
        };
    }
}
