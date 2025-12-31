<?php

namespace App\Filament\Resources\Concerns;

use App\Models\ResourceConfiguration;
use App\Models\RoleResourceAccess;
use Illuminate\Support\Facades\Cache;

/**
 * Trait to check if a resource should be accessible for the current user
 * Add this trait to any Filament Resource that needs role-based access control
 */
trait ChecksResourceAccess
{
    /**
     * Check if the current user can view any records
     */
    public static function canViewAny(): bool
    {
        return static::checkPermission('can_view');
    }

    /**
     * Check if the current user can create records
     */
    public static function canCreate(): bool
    {
        return static::checkPermission('can_create');
    }

    /**
     * Check if the current user can edit records
     */
    public static function canEdit($record): bool
    {
        return static::checkPermission('can_edit');
    }

    /**
     * Check if the current user can delete records
     */
    public static function canDelete($record): bool
    {
        return static::checkPermission('can_delete');
    }

    /**
     * Check a specific permission for the current user
     */
    protected static function checkPermission(string $permission): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super-admin bypass - always has access
        if ($user->hasRole('super-admin')) {
            return true;
        }

        $resourceClass = static::class;
        $cacheKey = "resource_access_{$user->id}_{$resourceClass}_{$permission}";

        return Cache::remember($cacheKey, 3600, function () use ($user, $resourceClass, $permission) {
            // Find resource configuration
            $resourceConfig = ResourceConfiguration::where('resource_class', $resourceClass)->first();

            if (!$resourceConfig) {
                // Resource not configured, allow by default
                return true;
            }

            // Check if resource is globally active
            if (!$resourceConfig->is_active) {
                return false;
            }

            // Check role access
            $roleIds = $user->roles->pluck('id')->toArray();

            if (empty($roleIds)) {
                return false;
            }

            $roleAccess = RoleResourceAccess::whereIn('role_id', $roleIds)
                ->where('resource_configuration_id', $resourceConfig->id)
                ->first();

            if ($roleAccess) {
                return (bool) $roleAccess->$permission;
            }

            // Default: deny
            return false;
        });
    }

    /**
     * Check if this resource should be visible in navigation
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super-admin bypass
        if ($user->hasRole('super-admin')) {
            return true;
        }

        $resourceClass = static::class;
        $cacheKey = "resource_nav_{$user->id}_{$resourceClass}";

        return Cache::remember($cacheKey, 3600, function () use ($user, $resourceClass) {
            $resourceConfig = ResourceConfiguration::where('resource_class', $resourceClass)->first();

            if (!$resourceConfig || !$resourceConfig->is_active) {
                return false;
            }

            $roleIds = $user->roles->pluck('id')->toArray();

            if (empty($roleIds)) {
                return false;
            }

            $roleAccess = RoleResourceAccess::whereIn('role_id', $roleIds)
                ->where('resource_configuration_id', $resourceConfig->id)
                ->first();

            if ($roleAccess) {
                return $roleAccess->is_visible_in_navigation && $roleAccess->can_view;
            }

            return false;
        });
    }
}
