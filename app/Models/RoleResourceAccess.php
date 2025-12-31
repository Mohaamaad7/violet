<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Role Resource Access Model - Zero-Config Approach
 * 
 * Stores ONLY restricted resources (overrides).
 * If a resource is NOT in this table, it has FULL ACCESS by default.
 * 
 * Use resource_class directly instead of resource_configuration_id.
 */
class RoleResourceAccess extends Model
{
    protected $table = 'role_resource_access';

    protected $fillable = [
        'role_id',
        'resource_class', // The full class name, e.g., App\Filament\Resources\Products\ProductResource
        'resource_configuration_id', // Legacy - kept for backward compatibility
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'is_visible_in_navigation',
        'navigation_sort',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'is_visible_in_navigation' => 'boolean',
        'navigation_sort' => 'integer',
    ];

    // ==================== Relationships ====================

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // Legacy relationship - kept for backward compatibility
    public function resourceConfiguration(): BelongsTo
    {
        return $this->belongsTo(ResourceConfiguration::class);
    }

    // ==================== Scopes ====================

    /**
     * Get restricted resources for a role
     */
    public function scopeRestrictedForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId)
            ->where(function ($q) {
                $q->where('can_view', false)
                    ->orWhere('can_create', false)
                    ->orWhere('can_edit', false)
                    ->orWhere('can_delete', false);
            });
    }

    // ==================== Static Helpers ====================

    /**
     * Check if a resource has restrictions for a role
     */
    public static function getRestriction(int $roleId, string $resourceClass): ?self
    {
        return self::where('role_id', $roleId)
            ->where('resource_class', $resourceClass)
            ->first();
    }

    /**
     * Check if role can perform action on resource
     * Returns TRUE if no restriction found (default: full access)
     */
    public static function canPerform(int $roleId, string $resourceClass, string $permission): bool
    {
        $restriction = self::getRestriction($roleId, $resourceClass);

        if (!$restriction) {
            return true; // No restriction = full access
        }

        return (bool) $restriction->{$permission};
    }
}
