<?php

namespace App\Filament\Resources\Concerns;

use App\Services\DashboardConfigurationService;

/**
 * Trait to check if a resource should be accessible for the current user
 * 
 * Zero-Config Approach:
 * - Default: FULL ACCESS (all permissions true)
 * - Only checks database for explicit "deny" records
 * - No resource configuration table needed
 * - Just add this trait to get role-based access control
 * 
 * Note: This trait is OPTIONAL. The service can work without it.
 * Use it when you want explicit method overrides in your resource.
 */
trait ChecksResourceAccess
{
    /**
     * Check if the current user can view any records
     * Default: TRUE (allowed)
     */
    public static function canViewAny(): bool
    {
        return static::checkPermission('can_view');
    }

    /**
     * Check if the current user can create records
     * Default: TRUE (allowed)
     */
    public static function canCreate(): bool
    {
        return static::checkPermission('can_create');
    }

    /**
     * Check if the current user can edit records
     * Default: TRUE (allowed)
     */
    public static function canEdit($record): bool
    {
        return static::checkPermission('can_edit');
    }

    /**
     * Check if the current user can delete records
     * Default: TRUE (allowed)
     */
    public static function canDelete($record): bool
    {
        return static::checkPermission('can_delete');
    }

    /**
     * Check a specific permission for the current user
     * Uses DashboardConfigurationService for centralized logic
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

        // Use the service for centralized logic
        return app(DashboardConfigurationService::class)
            ->canAccessResource(static::class, $permission);
    }

    /**
     * Check if this resource should be visible in navigation
     * Default: TRUE (visible) - shown unless explicitly hidden
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

        // Use the service
        return app(DashboardConfigurationService::class)
            ->shouldShowResourceInNavigation(static::class);
    }
}
