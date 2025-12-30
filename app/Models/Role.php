<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // ==================== Dashboard Customization Relationships ====================

    /**
     * Widget defaults for this role
     */
    public function widgetDefaults(): HasMany
    {
        return $this->hasMany(RoleWidgetDefault::class);
    }

    /**
     * Resource access configurations for this role
     */
    public function resourceAccess(): HasMany
    {
        return $this->hasMany(RoleResourceAccess::class);
    }

    /**
     * Navigation groups accessible by this role
     */
    public function navigationGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            NavigationGroupConfiguration::class,
            'role_navigation_groups',
            'role_id',
            'navigation_group_id'
        )->withPivot(['is_visible', 'order_position'])
            ->withTimestamps();
    }

    /**
     * Navigation group pivot records
     */
    public function roleNavigationGroups(): HasMany
    {
        return $this->hasMany(RoleNavigationGroup::class);
    }
}
