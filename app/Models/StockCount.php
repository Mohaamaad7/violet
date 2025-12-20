<?php

namespace App\Models;

use App\Enums\StockCountScope;
use App\Enums\StockCountStatus;
use App\Enums\StockCountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockCount extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'warehouse_id',
        'type',
        'scope',
        'scope_ids',
        'status',
        'started_at',
        'completed_at',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'type' => StockCountType::class,
        'scope' => StockCountScope::class,
        'status' => StockCountStatus::class,
        'scope_ids' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Activity Log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "تم {$this->getEventLabel($eventName)} جلسة الجرد {$this->code}")
            ->useLogName('stock_counts');
    }

    protected function getEventLabel(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'إنشاء',
            'updated' => 'تحديث',
            'deleted' => 'حذف',
            default => $eventName,
        };
    }

    // ==========================================
    // Relations
    // ==========================================

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            StockCountStatus::DRAFT,
            StockCountStatus::IN_PROGRESS,
        ]);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', StockCountStatus::IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', StockCountStatus::COMPLETED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', StockCountStatus::APPROVED);
    }

    // ==========================================
    // Accessors
    // ==========================================

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Get counted items count
     */
    public function getCountedItemsAttribute(): int
    {
        return $this->items()->whereNotNull('counted_quantity')->count();
    }

    /**
     * Get count progress percentage
     */
    public function getProgressAttribute(): float
    {
        $total = $this->total_items;
        if ($total === 0)
            return 0;

        return round(($this->counted_items / $total) * 100, 1);
    }

    /**
     * Get items with differences
     */
    public function getDifferenceItemsAttribute(): int
    {
        return $this->items()
            ->whereNotNull('difference')
            ->where('difference', '!=', 0)
            ->count();
    }

    /**
     * Get total positive difference (surplus)
     */
    public function getTotalSurplusAttribute(): int
    {
        return $this->items()
            ->where('difference', '>', 0)
            ->sum('difference');
    }

    /**
     * Get total negative difference (shortage)
     */
    public function getTotalShortageAttribute(): int
    {
        return abs($this->items()
            ->where('difference', '<', 0)
            ->sum('difference'));
    }

    /**
     * Get total difference value
     */
    public function getTotalDifferenceValueAttribute(): float
    {
        return $this->items()->sum('difference_value') ?? 0;
    }

    // ==========================================
    // Status Helpers
    // ==========================================

    /**
     * Can item quantities be edited (only IN_PROGRESS)
     */
    public function canEditItems(): bool
    {
        return $this->status->canEditItems();
    }

    public function canEdit(): bool
    {
        return $this->status->canEdit();
    }

    public function canStart(): bool
    {
        return $this->status->canStart();
    }

    public function canComplete(): bool
    {
        return $this->status->canComplete();
    }

    public function canApprove(): bool
    {
        return $this->status->canApprove();
    }

    public function canCancel(): bool
    {
        return $this->status->canCancel();
    }

    public function isFullyCounted(): bool
    {
        return $this->total_items > 0 && $this->counted_items === $this->total_items;
    }

    // ==========================================
    // Code Generation
    // ==========================================

    /**
     * Generate next code for a warehouse
     * Format: SC-ddMMyy-HH-mm-XXX
     */
    public static function generateCode(): string
    {
        $prefix = 'SC';
        $datePart = now()->format('dmy-H-i');

        // Count how many stock counts created today
        $todayCount = static::whereDate('created_at', today())->count() + 1;
        $sequence = str_pad($todayCount, 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$datePart}-{$sequence}";
    }
}
