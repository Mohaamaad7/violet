<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\RoleWidgetDefault;
use App\Models\UserWidgetPreference;
use App\Models\WidgetConfiguration;
use Illuminate\Support\Facades\Cache;

/**
 * Trait to check if a widget should be visible for the current user
 * Add this trait to any widget that needs role-based visibility
 */
trait ChecksWidgetVisibility
{
    /**
     * Determine if the widget can be viewed by the current user
     */
    public static function canView(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Get the widget class name
        $widgetClass = static::class;

        // Use cache for performance
        $cacheKey = "widget_visibility_{$user->id}_{$widgetClass}";

        return Cache::remember($cacheKey, 3600, function () use ($user, $widgetClass) {
            // Find widget configuration
            $widgetConfig = WidgetConfiguration::where('widget_class', $widgetClass)->first();

            if (!$widgetConfig) {
                // Widget not configured, show by default
                return true;
            }

            // Check if widget is globally active
            if (!$widgetConfig->is_active) {
                return false;
            }

            // 1. Check user-specific preferences first
            $userPref = UserWidgetPreference::where('user_id', $user->id)
                ->where('widget_configuration_id', $widgetConfig->id)
                ->first();

            if ($userPref) {
                return $userPref->is_visible;
            }

            // 2. Check role defaults
            $roleIds = $user->roles->pluck('id')->toArray();

            if (!empty($roleIds)) {
                $roleDefault = RoleWidgetDefault::whereIn('role_id', $roleIds)
                    ->where('widget_configuration_id', $widgetConfig->id)
                    ->first();

                if ($roleDefault) {
                    return $roleDefault->is_visible;
                }
            }

            // 3. Default: show widget
            return true;
        });
    }
}
