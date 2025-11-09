<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountCode extends Model
{
    protected $fillable = [
        'influencer_id',
        'code',
        'type',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_order_amount',
        'commission_type',
        'commission_value',
        'usage_limit',
        'usage_limit_per_user',
        'times_used',
        'starts_at',
        'expires_at',
        'is_active',
        'applies_to_categories',
        'applies_to_products',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
        'times_used' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'applies_to_categories' => 'array',
        'applies_to_products' => 'array',
    ];

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CodeUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }
}
