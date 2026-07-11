<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ComboRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'is_active',
        'show_on_homepage',
        'discount_type',
        'discount_percentage',
        'fixed_price',
        'tiers',
        'max_uses_per_user',
        'priority',
        'starts_at',
        'ends_at',
        'meta_title',
        'meta_description',
        'custom_pixel_id',
    ];

    protected $hidden = [
        'discount_type',
        'discount_percentage',
        'fixed_price',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_on_homepage' => 'boolean',
        'discount_percentage' => 'integer',
        'tiers' => 'array',
        'max_uses_per_user' => 'integer',
        'priority' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the conditions for the combo rule.
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(ComboRuleCondition::class);
    }

    /**
     * Get the usages of this combo rule.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(ComboRuleUsage::class);
    }

    /**
     * Scope a query to only include active combo rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope to order rules by priority (highest first) and then by ID.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('priority')->orderByDesc('id');
    }
}
