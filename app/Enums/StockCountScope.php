<?php

namespace App\Enums;

enum StockCountScope: string
{
    case ALL = 'all';
    case CATEGORY = 'category';
    case PRODUCTS = 'products';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::ALL => __('messages.stock_count.scope.all'),
            self::CATEGORY => __('messages.stock_count.scope.category'),
            self::PRODUCTS => __('messages.stock_count.scope.products'),
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::ALL => __('messages.stock_count.scope.all_desc'),
            self::CATEGORY => __('messages.stock_count.scope.category_desc'),
            self::PRODUCTS => __('messages.stock_count.scope.products_desc'),
        };
    }

    /**
     * Check if scope requires selection
     */
    public function requiresSelection(): bool
    {
        return $this !== self::ALL;
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
