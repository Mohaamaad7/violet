<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Resource Configuration Model
 * 
 * Stores all available Filament resources for dynamic navigation control.
 */
class ResourceConfiguration extends Model
{
    protected $fillable = [
        'resource_class',
        'resource_name',
        'navigation_group',
        'icon',
        'is_active',
        'default_navigation_sort',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_navigation_sort' => 'integer',
    ];

    // ==================== Relationships ====================

    /**
     * Role access configurations for this resource
     */
    public function roleAccess(): HasMany
    {
        return $this->hasMany(RoleResourceAccess::class);
    }

    // ==================== Scopes ====================

    /**
     * Only active resources
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter by navigation group
     */
    public function scopeInNavigationGroup($query, string $group)
    {
        return $query->where('navigation_group', $group);
    }

    // ==================== Helpers ====================

    /**
     * Get ordered active resources
     */
    public static function getOrderedActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
            ->orderBy('default_navigation_sort')
            ->get();
    }

    /**
     * Check if a resource class is registered
     */
    public static function isRegistered(string $resourceClass): bool
    {
        return static::where('resource_class', $resourceClass)->exists();
    }
}
