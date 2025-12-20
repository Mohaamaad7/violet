<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_count_id',
        'product_id',
        'variant_id',
        'system_quantity',
        'counted_quantity',
        'difference',
        'difference_value',
        'notes',
        'counted_by',
        'counted_at',
    ];

    protected $casts = [
        'system_quantity' => 'integer',
        'counted_quantity' => 'integer',
        'difference' => 'integer',
        'difference_value' => 'decimal:2',
        'counted_at' => 'datetime',
    ];

    // ==========================================
    // Relations
    // ==========================================

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    /**
     * Get the stock movement for this item (after approval)
     * Uses accessor pattern for reliable data retrieval
     */
    public function getMovementAttribute()
    {
        if (!isset($this->attributes['_movement'])) {
            $query = StockMovement::where('product_id', $this->product_id)
                ->where('reference_type', StockCount::class)
                ->where('reference_id', $this->stock_count_id);

            if ($this->variant_id) {
                $query->where('variant_id', $this->variant_id);
            } else {
                $query->whereNull('variant_id');
            }

            $this->attributes['_movement'] = $query->first();
        }

        return $this->attributes['_movement'] ?? null;
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopeCounted($query)
    {
        return $query->whereNotNull('counted_quantity');
    }

    public function scopeUncounted($query)
    {
        return $query->whereNull('counted_quantity');
    }

    public function scopeWithDifference($query)
    {
        return $query->whereNotNull('difference')
            ->where('difference', '!=', 0);
    }

    public function scopeShortage($query)
    {
        return $query->where('difference', '<', 0);
    }

    public function scopeSurplus($query)
    {
        return $query->where('difference', '>', 0);
    }

    // ==========================================
    // Accessors
    // ==========================================

    /**
     * Get display name (product name + variant if exists)
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product?->name ?? 'Unknown';

        if ($this->variant) {
            $name .= ' - ' . $this->variant->name;
        }

        return $name;
    }

    /**
     * Get SKU (product or variant)
     */
    public function getSkuAttribute(): string
    {
        return $this->variant?->sku ?? $this->product?->sku ?? '-';
    }

    /**
     * Get unit cost price
     */
    public function getCostPriceAttribute(): float
    {
        return $this->product?->cost_price ?? 0;
    }

    /**
     * Get difference status (shortage, surplus, matched)
     */
    public function getDifferenceStatusAttribute(): string
    {
        if ($this->difference === null) {
            return 'uncounted';
        }

        if ($this->difference < 0) {
            return 'shortage';
        }

        if ($this->difference > 0) {
            return 'surplus';
        }

        return 'matched';
    }

    /**
     * Get difference color for UI
     */
    public function getDifferenceColorAttribute(): string
    {
        return match ($this->difference_status) {
            'shortage' => 'danger',
            'surplus' => 'warning',
            'matched' => 'success',
            default => 'gray',
        };
    }

    // ==========================================
    // Mutators
    // ==========================================

    /**
     * Set counted quantity and calculate difference
     */
    public function setCountedQuantityAttribute($value): void
    {
        $this->attributes['counted_quantity'] = $value;

        if ($value !== null) {
            $difference = $value - $this->system_quantity;
            $this->attributes['difference'] = $difference;

            // Calculate value difference
            $costPrice = $this->product?->cost_price ?? 0;
            $this->attributes['difference_value'] = $difference * $costPrice;

            // Set counted timestamp if not set
            if (!$this->counted_at) {
                $this->attributes['counted_at'] = now();
            }

            // Set counted by if not set
            if (!$this->counted_by && auth()->check()) {
                $this->attributes['counted_by'] = auth()->id();
            }
        }
    }
}
