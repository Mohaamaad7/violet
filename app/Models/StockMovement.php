<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\VarianceReasonType;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockMovement extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'batch_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'created_by',
        'notes',
        'reason_type',
        'responsible_id',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'unit_cost' => 'decimal:2',
        'reason_type' => VarianceReasonType::class,
    ];

    /**
     * Activity Log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('stock_movements');
    }

    // ==========================================
    // Relations
    // ==========================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * الموظف المسؤول (للعجز)
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * فلترة حسب سبب الفرق
     */
    public function scopeOfReasonType($query, string $reasonType)
    {
        return $query->where('reason_type', $reasonType);
    }

    /**
     * حركات مسؤولية الموظفين فقط
     */
    public function scopeEmployeeLiability($query)
    {
        return $query->where('reason_type', VarianceReasonType::EMPLOYEE_LIABILITY->value);
    }

    /**
     * حركات الجرد فقط
     */
    public function scopeStockCount($query)
    {
        return $query->where('type', 'stock_count');
    }

    // ==========================================
    // Accessors
    // ==========================================

    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->type) {
            'restock' => 'success',
            'sale' => 'info',
            'return' => 'warning',
            'adjustment', 'stock_count' => 'primary',
            'expired', 'damaged' => 'danger',
            default => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'restock' => __('messages.stock_movement.type.restock'),
            'sale' => __('messages.stock_movement.type.sale'),
            'return' => __('messages.stock_movement.type.return'),
            'adjustment' => __('messages.stock_movement.type.adjustment'),
            'stock_count' => __('messages.stock_movement.type.stock_count'),
            'expired' => __('messages.stock_movement.type.expired'),
            'damaged' => __('messages.stock_movement.type.damaged'),
            default => $this->type,
        };
    }

    public function getFormattedQuantityAttribute(): string
    {
        return ($this->quantity > 0 ? '+' : '') . $this->quantity;
    }

    /**
     * Get display name for the item (product + variant if exists)
     */
    public function getItemNameAttribute(): string
    {
        $name = $this->product?->name ?? 'Unknown';

        if ($this->variant) {
            $name .= ' - ' . $this->variant->name;
        }

        return $name;
    }
}

