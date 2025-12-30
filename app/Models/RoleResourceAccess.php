<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Role Resource Access Model
 * 
 * Defines which resources each role can access and with what permissions.
 */
class RoleResourceAccess extends Model
{
    protected $table = 'role_resource_access';

    protected $fillable = [
        'role_id',
        'resource_configuration_id',
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

    /**
     * The role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The resource configuration
     */
    public function resourceConfiguration(): BelongsTo
    {
        return $this->belongsTo(ResourceConfiguration::class);
    }

    // ==================== Scopes ====================

    /**
     * Get visible resources for a role
     */
    public function scopeVisibleForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId)
            ->where('is_visible_in_navigation', true)
            ->orderBy('navigation_sort');
    }

    /**
     * Get resources with view permission for a role
     */
    public function scopeViewableForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId)
            ->where('can_view', true);
    }

    // ==================== Helpers ====================

    /**
     * Check if role has any permission on the resource
     */
    public function hasAnyPermission(): bool
    {
        return $this->can_view || $this->can_create || $this->can_edit || $this->can_delete;
    }
}
