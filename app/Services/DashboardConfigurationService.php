<?php

namespace App\Services;

use App\Models\NavigationGroupConfiguration;
use App\Models\ResourceConfiguration;
use App\Models\RoleNavigationGroup;
use App\Models\RoleResourceAccess;
use App\Models\RoleWidgetDefault;
use App\Models\User;
use App\Models\UserWidgetPreference;
use App\Models\WidgetConfiguration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Dashboard Configuration Service
 * 
 * Provides dynamic widget, resource, and navigation group management
 * based on user preferences and role defaults.
 */
class DashboardConfigurationService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    protected int $cacheDuration = 3600;

    // ==================== Widget Methods ====================

    /**
     * Get widgets for a specific user
     * Priority: User Preferences > Role Defaults > System Defaults
     *
     * @param User $user
     * @return array Array of widget class names
     */
    public function getWidgetsForUser(User $user): array
    {
        $cacheKey = "user_widgets_{$user->id}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($user) {
            // 1. Check user-specific preferences first
            $userPreferences = UserWidgetPreference::where('user_id', $user->id)
                ->where('is_visible', true)
                ->with('widgetConfiguration')
                ->orderBy('order_position')
                ->get();

            if ($userPreferences->isNotEmpty()) {
                return $userPreferences
                    ->filter(fn($pref) => $pref->widgetConfiguration?->is_active)
                    ->map(fn($pref) => [
                        'class' => $pref->widgetConfiguration->widget_class,
                        'column_span' => $pref->column_span,
                        'order' => $pref->order_position,
                    ])
                    ->values()
                    ->toArray();
            }

            // 2. Fall back to role defaults
            $roleIds = $user->roles->pluck('id')->toArray();

            if (!empty($roleIds)) {
                $roleDefaults = RoleWidgetDefault::whereIn('role_id', $roleIds)
                    ->where('is_visible', true)
                    ->with('widgetConfiguration')
                    ->orderBy('order_position')
                    ->get();

                if ($roleDefaults->isNotEmpty()) {
                    return $roleDefaults
                        ->filter(fn($def) => $def->widgetConfiguration?->is_active)
                        ->unique('widget_configuration_id')
                        ->map(fn($def) => [
                            'class' => $def->widgetConfiguration->widget_class,
                            'column_span' => $def->column_span,
                            'order' => $def->order_position,
                        ])
                        ->values()
                        ->toArray();
                }
            }

            // 3. Fall back to system defaults (all active widgets)
            return WidgetConfiguration::active()
                ->orderBy('default_order')
                ->get()
                ->map(fn($widget) => [
                    'class' => $widget->widget_class,
                    'column_span' => $widget->default_column_span,
                    'order' => $widget->default_order,
                ])
                ->toArray();
        });
    }

    /**
     * Get widget classes only for Filament panel
     */
    public function getWidgetClassesForUser(User $user): array
    {
        $widgets = $this->getWidgetsForUser($user);
        return array_column($widgets, 'class');
    }

    // ==================== Resource Methods ====================

    /**
     * Get accessible resources for a user
     *
     * @param User $user
     * @return array Array of resource configurations with permissions
     */
    public function getResourcesForUser(User $user): array
    {
        $cacheKey = "user_resources_{$user->id}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($user) {
            $roleIds = $user->roles->pluck('id')->toArray();

            if (empty($roleIds)) {
                return [];
            }

            // Get all resource access for user's roles
            $resourceAccess = RoleResourceAccess::whereIn('role_id', $roleIds)
                ->where('can_view', true)
                ->with('resourceConfiguration')
                ->get();

            // Merge permissions from all roles (most permissive wins)
            $mergedAccess = [];

            foreach ($resourceAccess as $access) {
                $resourceClass = $access->resourceConfiguration?->resource_class;

                if (!$resourceClass || !$access->resourceConfiguration->is_active) {
                    continue;
                }

                if (!isset($mergedAccess[$resourceClass])) {
                    $mergedAccess[$resourceClass] = [
                        'class' => $resourceClass,
                        'name' => $access->resourceConfiguration->resource_name,
                        'navigation_group' => $access->resourceConfiguration->navigation_group,
                        'can_view' => false,
                        'can_create' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                        'is_visible_in_navigation' => false,
                        'navigation_sort' => $access->navigation_sort,
                    ];
                }

                // Merge permissions (most permissive)
                $mergedAccess[$resourceClass]['can_view'] = $mergedAccess[$resourceClass]['can_view'] || $access->can_view;
                $mergedAccess[$resourceClass]['can_create'] = $mergedAccess[$resourceClass]['can_create'] || $access->can_create;
                $mergedAccess[$resourceClass]['can_edit'] = $mergedAccess[$resourceClass]['can_edit'] || $access->can_edit;
                $mergedAccess[$resourceClass]['can_delete'] = $mergedAccess[$resourceClass]['can_delete'] || $access->can_delete;
                $mergedAccess[$resourceClass]['is_visible_in_navigation'] = $mergedAccess[$resourceClass]['is_visible_in_navigation'] || $access->is_visible_in_navigation;
            }

            return array_values($mergedAccess);
        });
    }

    /**
     * Get resource classes visible in navigation for a user
     */
    public function getVisibleResourceClassesForUser(User $user): array
    {
        $resources = $this->getResourcesForUser($user);

        return collect($resources)
            ->filter(fn($r) => $r['is_visible_in_navigation'])
            ->pluck('class')
            ->toArray();
    }

    /**
     * Check if user can perform action on a resource
     */
    public function canUserAccessResource(User $user, string $resourceClass, string $action = 'view'): bool
    {
        $resources = $this->getResourcesForUser($user);

        $resource = collect($resources)->firstWhere('class', $resourceClass);

        if (!$resource) {
            return false;
        }

        return match ($action) {
            'view' => $resource['can_view'],
            'create' => $resource['can_create'],
            'edit' => $resource['can_edit'],
            'delete' => $resource['can_delete'],
            default => false,
        };
    }

    // ==================== Navigation Group Methods ====================

    /**
     * Get navigation groups for a user
     *
     * @param User $user
     * @return array Array of navigation groups with labels
     */
    public function getNavigationGroupsForUser(User $user): array
    {
        $cacheKey = "user_nav_groups_{$user->id}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($user) {
            $roleIds = $user->roles->pluck('id')->toArray();

            if (empty($roleIds)) {
                // Return all active groups for users without roles
                return NavigationGroupConfiguration::active()
                    ->ordered()
                    ->get()
                    ->map(fn($group) => [
                        'key' => $group->group_key,
                        'label' => $group->getLabel(),
                        'icon' => $group->icon,
                        'order' => $group->default_order,
                    ])
                    ->toArray();
            }

            // Get role-specific navigation groups
            $roleNavGroups = RoleNavigationGroup::whereIn('role_id', $roleIds)
                ->where('is_visible', true)
                ->with('navigationGroup')
                ->orderBy('order_position')
                ->get();

            if ($roleNavGroups->isNotEmpty()) {
                return $roleNavGroups
                    ->filter(fn($rng) => $rng->navigationGroup?->is_active)
                    ->unique('navigation_group_id')
                    ->map(fn($rng) => [
                        'key' => $rng->navigationGroup->group_key,
                        'label' => $rng->navigationGroup->getLabel(),
                        'icon' => $rng->navigationGroup->icon,
                        'order' => $rng->order_position,
                    ])
                    ->sortBy('order')
                    ->values()
                    ->toArray();
            }

            // Fall back to all active groups
            return NavigationGroupConfiguration::active()
                ->ordered()
                ->get()
                ->map(fn($group) => [
                    'key' => $group->group_key,
                    'label' => $group->getLabel(),
                    'icon' => $group->icon,
                    'order' => $group->default_order,
                ])
                ->toArray();
        });
    }

    // ==================== Discovery Methods ====================

    /**
     * Auto-discover and register widgets from the Filament/Widgets directory
     *
     * @return int Number of widgets registered
     */
    public function discoverWidgets(): int
    {
        $widgetsPath = app_path('Filament/Widgets');
        $registered = 0;

        if (!File::isDirectory($widgetsPath)) {
            return 0;
        }

        $files = File::allFiles($widgetsPath);

        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file->getPathname(), 'App\\Filament\\Widgets');

            if (!$className || !class_exists($className)) {
                continue;
            }

            // Skip if already registered
            if (WidgetConfiguration::where('widget_class', $className)->exists()) {
                continue;
            }

            // Determine widget name from class name
            $widgetName = $this->getHumanReadableName($className);
            $widgetGroup = $this->guessWidgetGroup($className);

            WidgetConfiguration::create([
                'widget_class' => $className,
                'widget_name' => $widgetName,
                'widget_group' => $widgetGroup,
                'is_active' => true,
                'default_order' => $registered * 10,
                'default_column_span' => 1,
            ]);

            $registered++;
        }

        // Clear cache
        $this->clearAllCaches();

        return $registered;
    }

    /**
     * Auto-discover and register resources from the Filament/Resources directory
     *
     * @return int Number of resources registered
     */
    public function discoverResources(): int
    {
        $resourcesPath = app_path('Filament/Resources');
        $registered = 0;

        if (!File::isDirectory($resourcesPath)) {
            return 0;
        }

        // Get all *Resource.php files
        $files = File::allFiles($resourcesPath);
        $resourceFiles = collect($files)->filter(fn($f) => str_ends_with($f->getFilename(), 'Resource.php'));

        foreach ($resourceFiles as $file) {
            $className = $this->getClassNameFromFile($file->getPathname(), 'App\\Filament\\Resources');

            if (!$className || !class_exists($className)) {
                continue;
            }

            // Skip if already registered
            if (ResourceConfiguration::where('resource_class', $className)->exists()) {
                continue;
            }

            // Get resource info using reflection
            $resourceName = $this->getHumanReadableName($className, 'Resource');
            $navigationGroup = $this->getResourceNavigationGroup($className);

            ResourceConfiguration::create([
                'resource_class' => $className,
                'resource_name' => $resourceName,
                'navigation_group' => $navigationGroup,
                'is_active' => true,
                'default_navigation_sort' => $registered * 10,
            ]);

            $registered++;
        }

        // Clear cache
        $this->clearAllCaches();

        return $registered;
    }

    /**
     * Discover and register navigation groups from translation files
     *
     * @return int Number of groups registered
     */
    public function discoverNavigationGroups(): int
    {
        $registered = 0;

        // Get navigation groups from translation
        $arGroups = __('admin.nav', [], 'ar');
        $enGroups = __('admin.nav', [], 'en');

        if (!is_array($arGroups) || !is_array($enGroups)) {
            return 0;
        }

        foreach ($arGroups as $key => $arLabel) {
            // Skip if already registered
            if (NavigationGroupConfiguration::where('group_key', $key)->exists()) {
                continue;
            }

            $enLabel = $enGroups[$key] ?? ucfirst($key);

            NavigationGroupConfiguration::create([
                'group_key' => $key,
                'group_label_ar' => $arLabel,
                'group_label_en' => $enLabel,
                'is_active' => true,
                'default_order' => $registered * 10,
            ]);

            $registered++;
        }

        // Clear cache
        $this->clearAllCaches();

        return $registered;
    }

    // ==================== Cache Methods ====================

    /**
     * Clear cache for a specific user
     */
    public function clearUserCache(User $user): void
    {
        Cache::forget("user_widgets_{$user->id}");
        Cache::forget("user_resources_{$user->id}");
        Cache::forget("user_nav_groups_{$user->id}");
    }

    /**
     * Clear all dashboard configuration caches
     */
    public function clearAllCaches(): void
    {
        // This is a simplified version - in production you'd want to track cache keys
        Cache::flush();
    }

    // ==================== Helper Methods ====================

    /**
     * Get class name from file path
     */
    protected function getClassNameFromFile(string $filePath, string $baseNamespace): ?string
    {
        $content = File::get($filePath);

        // Extract namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = $matches[1];
        } else {
            return null;
        }

        // Extract class name
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            $className = $matches[1];
        } else {
            return null;
        }

        return $namespace . '\\' . $className;
    }

    /**
     * Get human-readable name from class name
     */
    protected function getHumanReadableName(string $className, string $suffix = 'Widget'): string
    {
        $shortName = class_basename($className);
        $name = str_replace($suffix, '', $shortName);

        // Convert CamelCase to Title Case
        return preg_replace('/(?<!^)([A-Z])/', ' $1', $name);
    }

    /**
     * Guess widget group from class name
     */
    protected function guessWidgetGroup(string $className): string
    {
        $shortName = strtolower(class_basename($className));

        if (str_contains($shortName, 'sales') || str_contains($shortName, 'order') || str_contains($shortName, 'revenue')) {
            return 'sales';
        }

        if (str_contains($shortName, 'stock') || str_contains($shortName, 'inventory') || str_contains($shortName, 'warehouse')) {
            return 'inventory';
        }

        if (str_contains($shortName, 'customer') || str_contains($shortName, 'user')) {
            return 'customers';
        }

        return 'general';
    }

    /**
     * Get resource navigation group using class method if available
     */
    protected function getResourceNavigationGroup(string $className): ?string
    {
        if (!class_exists($className)) {
            return null;
        }

        try {
            if (method_exists($className, 'getNavigationGroup')) {
                // Set locale to en to get the key
                $originalLocale = app()->getLocale();
                app()->setLocale('en');

                $group = $className::getNavigationGroup();

                app()->setLocale($originalLocale);

                return $group;
            }
        } catch (\Throwable $e) {
            // Ignore errors
        }

        return null;
    }
}
