<?php

namespace App\Enums;

enum StockCountType: string
{
    case FULL = 'full';
    case PARTIAL = 'partial';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::FULL => __('messages.stock_count.type.full'),
            self::PARTIAL => __('messages.stock_count.type.partial'),
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::FULL => __('messages.stock_count.type.full_desc'),
            self::PARTIAL => __('messages.stock_count.type.partial_desc'),
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match ($this) {
            self::FULL => 'heroicon-o-cube',
            self::PARTIAL => 'heroicon-o-squares-2x2',
        };
    }

    /**
     * Get all values for select options
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}
