<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'order_item_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'price',
        'condition',
        'restocked',
        'restocked_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'restocked' => 'boolean',
        'restocked_at' => 'datetime',
    ];

    // Relations
    public function return(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class, 'return_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->price;
    }

    public function getConditionBadgeColorAttribute(): string
    {
        return match($this->condition) {
            'good' => 'success',
            'opened' => 'warning',
            'damaged' => 'danger',
            default => 'secondary',
        };
    }

    public function canBeRestocked(): bool
    {
        return in_array($this->condition, ['good', 'opened']) && !$this->restocked;
    }
}
