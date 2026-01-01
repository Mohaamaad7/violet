<?php

namespace App\Services;

use App\Models\RoleResourceAccess;
use App\Models\RoleWidgetDefault;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * Dashboard Configuration Service - Zero-Config with Smart Grouping
 * 
 * Features:
 * - Runtime discovery of widgets and resources
 * - Smart group guessing from class names
 * - Optional override via $dashboardGroup property
 * - Localized display names
 * - Everything visible by default
 */
class DashboardConfigurationService
{
    /**
     * Navigation group mapping for smart guessing
     */
    protected array $groupKeywords = [
        'sales' => ['sales', 'order', 'revenue', 'payment', 'return', 'coupon'],
        'inventory' => ['stock', 'warehouse', 'inventory', 'product', 'batch', 'movement'],
        'customers' => ['customer', 'user', 'client'],
        'catalog' => ['category', 'product', 'brand'],
        'content' => ['banner', 'slider', 'email', 'template'],
        'geography' => ['city', 'country', 'governorate'],
        'system' => ['role', 'permission', 'setting', 'config', 'user'],
    ];

    /**
     * Group display order
     */
    protected array $groupOrder = [
        'sales' => 1,
        'inventory' => 2,
        'catalog' => 3,
        'customers' => 4,
        'content' => 5,
        'geography' => 6,
        'system' => 7,
        'general' => 99,
    ];

    /**
     * Get visible widgets for the current user
     */
    public function getVisibleWidgetsForCurrentUser(): array
    {
        $user = auth()->user();

        if (!$user) {
            return $this->getDefaultWidgets();
        }

        $cacheKey = "visible_widgets_user_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            $allWidgets = $this->discoverAllWidgets();
            $visibleWidgets = [];

            foreach ($allWidgets as $widgetClass) {
                if ($this->isWidgetVisibleForUser($widgetClass, $user)) {
                    $visibleWidgets[] = $widgetClass;
                }
            }

            return $visibleWidgets;
        });
    }

    /**
     * Check if a widget is visible for a specific user
     */
    public function isWidgetVisibleForUser(string $widgetClass, User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        $roleIds = $user->roles->pluck('id')->toArray();

        if (empty($roleIds)) {
            return true;
        }

        $hiddenRecord = RoleWidgetDefault::where('widget_class', $widgetClass)
            ->whereIn('role_id', $roleIds)
            ->where('is_visible', false)
            ->first();

        return $hiddenRecord === null;
    }

    /**
     * Check if a resource is accessible for current user
     */
    public function canAccessResource(string $resourceClass, string $permission = 'can_view'): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->hasRole('super-admin')) {
            return true;
        }

        $roleIds = $user->roles->pluck('id')->toArray();

        if (empty($roleIds)) {
            return true;
        }

        $denyRecord = RoleResourceAccess::where('resource_class', $resourceClass)
            ->whereIn('role_id', $roleIds)
            ->where($permission, false)
            ->first();

        return $denyRecord === null;
    }

    /**
     * Should resource be shown in navigation?
     */
    public function shouldShowResourceInNavigation(string $resourceClass): bool
    {
        return $this->canAccessResource($resourceClass, 'can_view');
    }

    /**
     * Discover all widget classes from the codebase
     */
    public function discoverAllWidgets(): array
    {
        $cacheKey = 'all_widget_classes';

        return Cache::remember($cacheKey, 3600, function () {
            $widgets = [];
            $widgetPath = app_path('Filament/Widgets');

            if (!File::isDirectory($widgetPath)) {
                return $widgets;
            }

            $files = File::allFiles($widgetPath);

            foreach ($files as $file) {
                $className = $this->getClassNameFromFile($file->getPathname());

                if ($className && $this->isValidWidgetClass($className)) {
                    $widgets[] = $className;
                }
            }

            return $widgets;
        });
    }

    /**
     * Discover all resource classes from the codebase
     */
    public function discoverAllResources(): array
    {
        $cacheKey = 'all_resource_classes';

        return Cache::remember($cacheKey, 3600, function () {
            $resources = [];
            $resourcePath = app_path('Filament/Resources');

            if (!File::isDirectory($resourcePath)) {
                return $resources;
            }

            $files = File::allFiles($resourcePath);

            foreach ($files as $file) {
                if (Str::endsWith($file->getFilename(), 'Resource.php')) {
                    $className = $this->getClassNameFromFile($file->getPathname());

                    if ($className && $this->isValidResourceClass($className)) {
                        $resources[] = $className;
                    }
                }
            }

            return $resources;
        });
    }

    /**
     * Get all widgets with their visibility status for a role, grouped by category
     */
    public function getWidgetsWithStatusForRole(int $roleId): array
    {
        $allWidgets = $this->discoverAllWidgets();
        $result = [];

        foreach ($allWidgets as $widgetClass) {
            $override = RoleWidgetDefault::where('widget_class', $widgetClass)
                ->where('role_id', $roleId)
                ->first();

            $group = $this->getWidgetGroup($widgetClass);

            $result[] = [
                'class' => $widgetClass,
                'name' => $this->getWidgetDisplayName($widgetClass),
                'group' => $group,
                'group_label' => $this->getGroupLabel($group),
                'group_order' => $this->groupOrder[$group] ?? 99,
                'is_visible' => $override ? $override->is_visible : true,
                'has_override' => $override !== null,
            ];
        }

        // Sort by group order, then by name
        usort($result, function ($a, $b) {
            if ($a['group_order'] !== $b['group_order']) {
                return $a['group_order'] <=> $b['group_order'];
            }
            return strcmp($a['name'], $b['name']);
        });

        return $result;
    }

    /**
     * Get all resources with their access status for a role, grouped by category
     */
    public function getResourcesWithStatusForRole(int $roleId): array
    {
        $allResources = $this->discoverAllResources();
        $result = [];

        foreach ($allResources as $resourceClass) {
            // Skip DashboardConfig resources (system only)
            if (Str::contains($resourceClass, 'DashboardConfig')) {
                continue;
            }

            $override = RoleResourceAccess::where('resource_class', $resourceClass)
                ->where('role_id', $roleId)
                ->first();

            $group = $this->getResourceGroup($resourceClass);

            $result[] = [
                'class' => $resourceClass,
                'name' => $this->getResourceDisplayName($resourceClass),
                'group' => $group,
                'group_label' => $this->getGroupLabel($group),
                'group_order' => $this->groupOrder[$group] ?? 99,
                'can_view' => $override ? $override->can_view : true,
                'can_create' => $override ? $override->can_create : true,
                'can_edit' => $override ? $override->can_edit : true,
                'can_delete' => $override ? $override->can_delete : true,
                'has_override' => $override !== null,
            ];
        }

        // Sort by group order, then by name
        usort($result, function ($a, $b) {
            if ($a['group_order'] !== $b['group_order']) {
                return $a['group_order'] <=> $b['group_order'];
            }
            return strcmp($a['name'], $b['name']);
        });

        return $result;
    }

    /**
     * Get widgets grouped by category for UI
     */
    public function getWidgetsGroupedForRole(int $roleId): array
    {
        $widgets = $this->getWidgetsWithStatusForRole($roleId);
        $grouped = [];

        foreach ($widgets as $widget) {
            $group = $widget['group'];
            if (!isset($grouped[$group])) {
                $grouped[$group] = [
                    'key' => $group,
                    'label' => $widget['group_label'],
                    'order' => $widget['group_order'],
                    'items' => [],
                ];
            }
            $grouped[$group]['items'][] = $widget;
        }

        // Sort groups by order
        uasort($grouped, fn($a, $b) => $a['order'] <=> $b['order']);

        return $grouped;
    }

    /**
     * Get resources grouped by category for UI
     */
    public function getResourcesGroupedForRole(int $roleId): array
    {
        $resources = $this->getResourcesWithStatusForRole($roleId);
        $grouped = [];

        foreach ($resources as $resource) {
            $group = $resource['group'];
            if (!isset($grouped[$group])) {
                $grouped[$group] = [
                    'key' => $group,
                    'label' => $resource['group_label'],
                    'order' => $resource['group_order'],
                    'items' => [],
                ];
            }
            $grouped[$group]['items'][] = $resource;
        }

        uasort($grouped, fn($a, $b) => $a['order'] <=> $b['order']);

        return $grouped;
    }

    /**
     * Get all available groups
     */
    public function getAvailableGroups(): array
    {
        $groups = [];
        foreach ($this->groupOrder as $key => $order) {
            $groups[$key] = [
                'key' => $key,
                'label' => $this->getGroupLabel($key),
                'order' => $order,
            ];
        }
        return $groups;
    }

    /**
     * Set widget visibility for a role
     */
    public function setWidgetVisibility(int $roleId, string $widgetClass, bool $isVisible): void
    {
        if ($isVisible) {
            RoleWidgetDefault::where('role_id', $roleId)
                ->where('widget_class', $widgetClass)
                ->delete();
        } else {
            RoleWidgetDefault::updateOrCreate(
                ['role_id' => $roleId, 'widget_class' => $widgetClass],
                ['is_visible' => false]
            );
        }

        Cache::flush();
    }

    /**
     * Set resource access for a role
     */
    public function setResourceAccess(int $roleId, string $resourceClass, array $permissions): void
    {
        $isDefault = ($permissions['can_view'] ?? true) &&
            ($permissions['can_create'] ?? true) &&
            ($permissions['can_edit'] ?? true) &&
            ($permissions['can_delete'] ?? true);

        if ($isDefault) {
            RoleResourceAccess::where('role_id', $roleId)
                ->where('resource_class', $resourceClass)
                ->delete();
        } else {
            RoleResourceAccess::updateOrCreate(
                ['role_id' => $roleId, 'resource_class' => $resourceClass],
                $permissions
            );
        }

        Cache::flush();
    }

    /**
     * Bulk set all widgets in a group for a role
     */
    public function setGroupWidgetsVisibility(int $roleId, string $group, bool $isVisible): void
    {
        $widgets = $this->getWidgetsWithStatusForRole($roleId);

        foreach ($widgets as $widget) {
            if ($widget['group'] === $group) {
                $this->setWidgetVisibility($roleId, $widget['class'], $isVisible);
            }
        }
    }

    /**
     * Bulk set all resources in a group for a role
     */
    public function setGroupResourcesAccess(int $roleId, string $group, bool $fullAccess): void
    {
        $resources = $this->getResourcesWithStatusForRole($roleId);

        $permissions = [
            'can_view' => $fullAccess,
            'can_create' => $fullAccess,
            'can_edit' => $fullAccess,
            'can_delete' => $fullAccess,
        ];

        foreach ($resources as $resource) {
            if ($resource['group'] === $group) {
                $this->setResourceAccess($roleId, $resource['class'], $permissions);
            }
        }
    }

    /**
     * Get class name from file path
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);

        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            $namespace = $namespaceMatch[1];
        } else {
            return null;
        }

        if (preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            $className = $classMatch[1];
            return $namespace . '\\' . $className;
        }

        return null;
    }

    /**
     * Check if class is a valid Filament widget
     */
    protected function isValidWidgetClass(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($className);

            if ($reflection->isAbstract()) {
                return false;
            }

            if (!$reflection->isSubclassOf(\Filament\Widgets\Widget::class)) {
                return false;
            }

            // Check if it's discoverable
            if ($reflection->hasProperty('isDiscovered')) {
                $prop = $reflection->getProperty('isDiscovered');
                $prop->setAccessible(true);

                // Get static property value
                $defaultProperties = $reflection->getDefaultProperties();
                if (isset($defaultProperties['isDiscovered']) && $defaultProperties['isDiscovered'] === false) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if class is a valid Filament resource
     */
    protected function isValidResourceClass(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($className);

            if ($reflection->isAbstract()) {
                return false;
            }

            return $reflection->isSubclassOf(\Filament\Resources\Resource::class);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get widget group using Hybrid approach:
     * 1. Check for explicit $dashboardGroup property
     * 2. Smart guess from class name
     */
    protected function getWidgetGroup(string $widgetClass): string
    {
        try {
            $reflection = new ReflectionClass($widgetClass);

            // 1. Check for explicit property
            $defaultProperties = $reflection->getDefaultProperties();
            if (isset($defaultProperties['dashboardGroup'])) {
                return $defaultProperties['dashboardGroup'];
            }

            // 2. Smart guess from class name
            $shortName = strtolower(class_basename($widgetClass));

            foreach ($this->groupKeywords as $group => $keywords) {
                foreach ($keywords as $keyword) {
                    if (Str::contains($shortName, $keyword)) {
                        return $group;
                    }
                }
            }

            return 'general';
        } catch (\Exception $e) {
            return 'general';
        }
    }

    /**
     * Get resource group from its Navigation Group
     */
    protected function getResourceGroup(string $resourceClass): string
    {
        try {
            if (method_exists($resourceClass, 'getNavigationGroup')) {
                $navGroup = $resourceClass::getNavigationGroup();

                if ($navGroup) {
                    // Map navigation group to our groups
                    $navGroupLower = strtolower($navGroup);

                    // Check against translation keys
                    $mappings = [
                        'المبيعات' => 'sales',
                        'sales' => 'sales',
                        'المخزون' => 'inventory',
                        'inventory' => 'inventory',
                        'الكتالوج' => 'catalog',
                        'catalog' => 'catalog',
                        'العملاء' => 'customers',
                        'customers' => 'customers',
                        'المحتوى' => 'content',
                        'content' => 'content',
                        'الإعدادات الجغرافية' => 'geography',
                        'geographic' => 'geography',
                        'النظام' => 'system',
                        'system' => 'system',
                        'الإعدادات' => 'system',
                        'settings' => 'system',
                    ];

                    foreach ($mappings as $key => $group) {
                        if (Str::contains($navGroupLower, strtolower($key))) {
                            return $group;
                        }
                    }
                }
            }

            // Fallback: guess from class name
            $shortName = strtolower(class_basename($resourceClass));

            foreach ($this->groupKeywords as $group => $keywords) {
                foreach ($keywords as $keyword) {
                    if (Str::contains($shortName, $keyword)) {
                        return $group;
                    }
                }
            }

            return 'system';
        } catch (\Exception $e) {
            return 'system';
        }
    }

    /**
     * Get localized display name for a widget
     */
    protected function getWidgetDisplayName(string $widgetClass): string
    {
        try {
            // 1. Check if widget has getHeading() method
            if (method_exists($widgetClass, 'getHeading')) {
                $instance = app($widgetClass);
                $heading = $instance->getHeading();
                if ($heading) {
                    return $heading;
                }
            }

            // 2. Fallback: Convert class name to readable format
            $shortName = class_basename($widgetClass);
            $name = Str::replaceLast('Widget', '', $shortName);
            return Str::headline($name);
        } catch (\Exception $e) {
            $shortName = class_basename($widgetClass);
            return Str::replaceLast('Widget', '', $shortName);
        }
    }

    /**
     * Get localized display name for a resource
     */
    protected function getResourceDisplayName(string $resourceClass): string
    {
        try {
            // 1. Check for getModelLabel() or getNavigationLabel()
            if (method_exists($resourceClass, 'getNavigationLabel')) {
                $label = $resourceClass::getNavigationLabel();
                if ($label) {
                    return $label;
                }
            }

            if (method_exists($resourceClass, 'getPluralModelLabel')) {
                $label = $resourceClass::getPluralModelLabel();
                if ($label) {
                    return $label;
                }
            }

            // 2. Fallback
            $shortName = class_basename($resourceClass);
            $name = Str::replaceLast('Resource', '', $shortName);
            return Str::headline($name);
        } catch (\Exception $e) {
            $shortName = class_basename($resourceClass);
            return Str::replaceLast('Resource', '', $shortName);
        }
    }

    /**
     * Get localized group label
     */
    protected function getGroupLabel(string $group): string
    {
        // Use admin.nav translations
        $key = "admin.nav.{$group}";
        $translated = __($key);

        // If translation exists, return it
        if ($translated !== $key) {
            return $translated;
        }

        // Fallback to headline
        return Str::headline($group);
    }

    /**
     * Get default widgets
     */
    protected function getDefaultWidgets(): array
    {
        return [
            \Filament\Widgets\AccountWidget::class,
        ];
    }
}
