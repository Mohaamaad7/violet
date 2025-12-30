<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Navigation Group Configuration Model
 * 
 * Stores navigation groups with i18n labels for dynamic sidebar management.
 */
class NavigationGroupConfiguration extends Model
{
    protected $fillable = [
        'group_key',
        'group_label_ar',
        'group_label_en',
        'icon',
        'is_active',
        'default_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_order' => 'integer',
    ];

    // ==================== Relationships ====================

    /**
     * Roles that have access to this navigation group
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_navigation_groups',
            'navigation_group_id',
            'role_id'
        )->withPivot(['is_visible', 'order_position'])
            ->withTimestamps();
    }

    /**
     * Role navigation group pivot records
     */
    public function roleNavigationGroups(): HasMany
    {
        return $this->hasMany(RoleNavigationGroup::class, 'navigation_group_id');
    }

    // ==================== Scopes ====================

    /**
     * Only active groups
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Ordered by default
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('default_order');
    }

    // ==================== Helpers ====================

    /**
     * Get label based on current locale
     */
    public function getLabel(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->group_label_ar : $this->group_label_en;
    }

    /**
     * Get all active and ordered groups
     */
    public static function getOrderedActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
            ->ordered()
            ->get();
    }
}
