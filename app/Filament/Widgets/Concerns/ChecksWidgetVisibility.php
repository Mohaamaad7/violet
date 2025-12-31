<?php

namespace App\Filament\Widgets\Concerns;

use App\Services\DashboardConfigurationService;

/**
 * Trait to check if a widget should be visible for the current user
 * 
 * Zero-Config Approach:
 * - Default: VISIBLE (returns true)
 * - Only checks database for explicit "hide" records
 * - No widget configuration table needed
 * - Just add this trait to get role-based visibility
 */
trait ChecksWidgetVisibility
{
    /**
     * Determine if the widget can be viewed by the current user
     * 
     * Uses DashboardConfigurationService for centralized logic
     * Default: VISIBLE unless explicitly hidden in database
     */
    public static function canView(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super-admin sees everything
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Use the service for centralized logic
        return app(DashboardConfigurationService::class)
            ->isWidgetVisibleForUser(static::class, $user);
    }
}
