<?php

namespace App\Enums;

enum StockCountStatus: string
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => __('messages.stock_count.status.draft'),
            self::IN_PROGRESS => __('messages.stock_count.status.in_progress'),
            self::COMPLETED => __('messages.stock_count.status.completed'),
            self::APPROVED => __('messages.stock_count.status.approved'),
            self::CANCELLED => __('messages.stock_count.status.cancelled'),
        };
    }

    /**
     * Get color for Filament badges
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::IN_PROGRESS => 'warning',
            self::COMPLETED => 'info',
            self::APPROVED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    /**
     * Get icon for display
     */
    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-pencil-square',
            self::IN_PROGRESS => 'heroicon-o-clock',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::APPROVED => 'heroicon-o-check-badge',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }

    /**
     * Check if item quantities can be edited
     * Only during IN_PROGRESS
     */
    public function canEditItems(): bool
    {
        return $this === self::IN_PROGRESS;
    }

    /**
     * Check if count metadata can be edited (notes, etc)
     */
    public function canEdit(): bool
    {
        return in_array($this, [self::DRAFT, self::IN_PROGRESS]);
    }

    /**
     * Check if count can be started
     */
    public function canStart(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Check if count can be completed
     */
    public function canComplete(): bool
    {
        return $this === self::IN_PROGRESS;
    }

    /**
     * Check if count can be approved
     */
    public function canApprove(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if count can be cancelled
     */
    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::IN_PROGRESS, self::COMPLETED]);
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

