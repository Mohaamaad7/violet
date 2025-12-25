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
        'exclude_products',
        'exclude_categories',
        'internal_notes',
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'commission_type' => 'percentage',
        'commission_value' => 0,
        'times_used' => 0,
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
        'exclude_products' => 'array',
        'exclude_categories' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

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

    // ==================== SCOPES ====================

    /**
     * Scope: Active coupons only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Valid coupons (active + within date range + not expired)
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Not yet started coupons
     */
    public function scopeNotStarted($query)
    {
        return $query->whereNotNull('starts_at')
            ->where('starts_at', '>', now());
    }

    /**
     * Scope: Expired coupons
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    // ==================== VALIDATION HELPERS ====================

    /**
     * Check if coupon is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check start date
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        // Check expiry date
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Check total usage limit
        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if customer can use this coupon
     */
    public function canBeUsedByCustomer(?int $customerId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($customerId === null) {
            // Guest users: check if per-user limit allows guest usage
            return true;
        }

        // Check per-customer usage limit
        if ($this->usage_limit_per_user !== null) {
            $customerUsageCount = $this->usages()
                ->where('user_id', $customerId)
                ->count();

            if ($customerUsageCount >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if product is applicable (not excluded)
     * Rule: Exclude wins (exclusion has priority)
     */
    public function isProductApplicable(int $productId, ?int $categoryId = null): bool
    {
        // Step 1: Check exclusions first (Exclude Wins)
        $excludedProducts = $this->exclude_products ?? [];
        if (in_array($productId, $excludedProducts)) {
            return false;
        }

        $excludedCategories = $this->exclude_categories ?? [];
        if ($categoryId && in_array($categoryId, $excludedCategories)) {
            return false;
        }

        // Step 2: If includes are specified, product must be in them
        $appliesProducts = $this->applies_to_products ?? [];
        $appliesCategories = $this->applies_to_categories ?? [];

        // If no includes specified, applies to all (after exclusion check)
        if (empty($appliesProducts) && empty($appliesCategories)) {
            return true;
        }

        // Check if product is in includes
        if (!empty($appliesProducts) && in_array($productId, $appliesProducts)) {
            return true;
        }

        // Check if category is in includes
        if (!empty($appliesCategories) && $categoryId && in_array($categoryId, $appliesCategories)) {
            return true;
        }

        return false;
    }

    /**
     * Check minimum order requirement
     */
    public function meetsMinOrderAmount(float $subtotal): bool
    {
        return $subtotal >= (float) $this->min_order_amount;
    }

    // ==================== ACCESSORS ====================

    /**
     * Check if this is a free shipping coupon
     */
    public function isFreeShipping(): bool
    {
        return $this->discount_type === 'free_shipping';
    }

    /**
     * Check if this is a percentage discount
     */
    public function isPercentage(): bool
    {
        return $this->discount_type === 'percentage';
    }

    /**
     * Check if this is a fixed amount discount
     */
    public function isFixed(): bool
    {
        return $this->discount_type === 'fixed';
    }

    /**
     * Get human-readable status
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }

        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            return 'exhausted';
        }

        return 'active';
    }
}
