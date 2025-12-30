<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Role Navigation Group Model
 * 
 * Pivot model for role-navigation group relationship with visibility settings.
 */
class RoleNavigationGroup extends Model
{
    protected $fillable = [
        'role_id',
        'navigation_group_id',
        'is_visible',
        'order_position',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'order_position' => 'integer',
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
     * The navigation group configuration
     */
    public function navigationGroup(): BelongsTo
    {
        return $this->belongsTo(NavigationGroupConfiguration::class, 'navigation_group_id');
    }

    // ==================== Scopes ====================

    /**
     * Get visible groups for a role
     */
    public function scopeVisibleForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId)
            ->where('is_visible', true)
            ->orderBy('order_position');
    }
}
