<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'governorate_id',
        'name_ar',
        'name_en',
        'shipping_cost',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function shippingAddresses(): HasMany
    {
        return $this->hasMany(ShippingAddress::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGovernorate($query, int $governorateId)
    {
        return $query->where('governorate_id', $governorateId);
    }

    // Helper Methods
    public function getLocalizedNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * Get the effective shipping cost (city custom or governorate default)
     */
    public function getEffectiveShippingCostAttribute(): float
    {
        return $this->shipping_cost ?? $this->governorate->shipping_cost;
    }
}
