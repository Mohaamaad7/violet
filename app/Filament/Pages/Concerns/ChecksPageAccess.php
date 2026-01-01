<?php

namespace App\Filament\Pages\Concerns;

use App\Services\DashboardConfigurationService;

/**
 * ChecksPageAccess Trait - Zero-Config Approach
 * 
 * Use this trait in Filament Pages to enable role-based access control.
 * Pages are accessible by default; use the Role Permissions page to restrict them.
 */
trait ChecksPageAccess
{
    /**
     * Check if the page should be registered in navigation
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super admin sees everything
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Check with the service
        return app(DashboardConfigurationService::class)
            ->canAccessPage(static::class);
    }

    /**
     * Check if the current user can access this page
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super admin has full access
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Check with the service
        return app(DashboardConfigurationService::class)
            ->canAccessPage(static::class);
    }
}
