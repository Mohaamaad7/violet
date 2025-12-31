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
 * Dashboard Configuration Service - Zero-Config Approach
 * 
 * This service provides runtime discovery of widgets and resources.
 * Everything is visible by default unless explicitly hidden in the database.
 * 
 * Philosophy:
 * - No artisan commands needed
 * - No base classes required
 * - Database stores EXCEPTIONS only (what's hidden), not what's visible
 * - Plug & Play: Create widget/resource → it appears automatically
 */
class DashboardConfigurationService
{
    /**
     * Get visible widgets for the current user
     * Returns array of widget class names that should be shown
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
     * DEFAULT: VISIBLE (true) unless explicitly hidden
     */
    public function isWidgetVisibleForUser(string $widgetClass, User $user): bool
    {
        // Super-admin sees everything
        if ($user->hasRole('super-admin')) {
            return true;
        }

        $roleIds = $user->roles->pluck('id')->toArray();

        if (empty($roleIds)) {
            return true; // No role? Show everything
        }

        // Check if there's an explicit "hide" record in the database
        $hiddenRecord = RoleWidgetDefault::where('widget_class', $widgetClass)
            ->whereIn('role_id', $roleIds)
            ->where('is_visible', false)
            ->first();

        // If found a record that says "hide" → hide it
        // Otherwise → show it (Default: Visible)
        return $hiddenRecord === null;
    }

    /**
     * Check if a resource is accessible for current user
     * DEFAULT: FULL ACCESS unless explicitly restricted
     */
    public function canAccessResource(string $resourceClass, string $permission = 'can_view'): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super-admin has full access
        if ($user->hasRole('super-admin')) {
            return true;
        }

        $roleIds = $user->roles->pluck('id')->toArray();

        if (empty($roleIds)) {
            return true; // No role? Allow by default
        }

        // Check if there's an explicit "deny" record
        $denyRecord = RoleResourceAccess::where('resource_class', $resourceClass)
            ->whereIn('role_id', $roleIds)
            ->where($permission, false)
            ->first();

        // If found a record that says "deny" → deny
        // Otherwise → allow (Default: Full Access)
        return $denyRecord === null;
    }

    /**
     * Should resource be shown in navigation?
     * DEFAULT: VISIBLE unless explicitly hidden
     */
    public function shouldShowResourceInNavigation(string $resourceClass): bool
    {
        return $this->canAccessResource($resourceClass, 'can_view');
    }

    /**
     * Discover all widget classes from the codebase
     * This is the runtime discovery - no database needed
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
                // Only include main resource files (not pages, schemas, etc.)
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
     * Get all widgets with their visibility status for a role
     * Used by the Role Permissions UI
     */
    public function getWidgetsWithStatusForRole(int $roleId): array
    {
        $allWidgets = $this->discoverAllWidgets();
        $result = [];

        foreach ($allWidgets as $widgetClass) {
            // Check if there's an override in the database
            $override = RoleWidgetDefault::where('widget_class', $widgetClass)
                ->where('role_id', $roleId)
                ->first();

            $result[] = [
                'class' => $widgetClass,
                'name' => $this->getWidgetDisplayName($widgetClass),
                'group' => $this->getWidgetGroup($widgetClass),
                'is_visible' => $override ? $override->is_visible : true, // Default: visible
                'has_override' => $override !== null,
            ];
        }

        return $result;
    }

    /**
     * Get all resources with their access status for a role
     * Used by the Role Permissions UI
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

            $result[] = [
                'class' => $resourceClass,
                'name' => $this->getResourceDisplayName($resourceClass),
                'group' => $this->getResourceGroup($resourceClass),
                'can_view' => $override ? $override->can_view : true,
                'can_create' => $override ? $override->can_create : true,
                'can_edit' => $override ? $override->can_edit : true,
                'can_delete' => $override ? $override->can_delete : true,
                'has_override' => $override !== null,
            ];
        }

        return $result;
    }

    /**
     * Set widget visibility for a role
     * Creates/updates/deletes override record as needed
     */
    public function setWidgetVisibility(int $roleId, string $widgetClass, bool $isVisible): void
    {
        if ($isVisible) {
            // Default is visible, so delete any override
            RoleWidgetDefault::where('role_id', $roleId)
                ->where('widget_class', $widgetClass)
                ->delete();
        } else {
            // Need to hide it - create override record
            RoleWidgetDefault::updateOrCreate(
                ['role_id' => $roleId, 'widget_class' => $widgetClass],
                ['is_visible' => false]
            );
        }

        // Clear cache
        Cache::forget("visible_widgets_user_*");
        Cache::flush(); // Simple approach - clear all for now
    }

    /**
     * Set resource access for a role
     */
    public function setResourceAccess(int $roleId, string $resourceClass, array $permissions): void
    {
        // Check if all permissions are true (default state)
        $isDefault = ($permissions['can_view'] ?? true) &&
            ($permissions['can_create'] ?? true) &&
            ($permissions['can_edit'] ?? true) &&
            ($permissions['can_delete'] ?? true);

        if ($isDefault) {
            // All permissions are default, delete the override
            RoleResourceAccess::where('role_id', $roleId)
                ->where('resource_class', $resourceClass)
                ->delete();
        } else {
            // Need custom permissions - create override
            RoleResourceAccess::updateOrCreate(
                ['role_id' => $roleId, 'resource_class' => $resourceClass],
                $permissions
            );
        }

        Cache::flush();
    }

    /**
     * Get class name from file path
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);

        // Extract namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            $namespace = $namespaceMatch[1];
        } else {
            return null;
        }

        // Extract class name
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

            // Must not be abstract
            if ($reflection->isAbstract()) {
                return false;
            }

            // Must be a Filament widget
            if (!$reflection->isSubclassOf(\Filament\Widgets\Widget::class)) {
                return false;
            }

            // Check if it's discoverable (not hidden)
            if ($reflection->hasProperty('isDiscovered')) {
                $prop = $reflection->getProperty('isDiscovered');
                $prop->setAccessible(true);
                if ($prop->getValue() === false) {
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
     * Get display name for a widget
     */
    protected function getWidgetDisplayName(string $widgetClass): string
    {
        $shortName = class_basename($widgetClass);

        // Remove "Widget" suffix and convert to readable format
        $name = Str::replaceLast('Widget', '', $shortName);
        return Str::headline($name);
    }

    /**
     * Get display name for a resource
     */
    protected function getResourceDisplayName(string $resourceClass): string
    {
        $shortName = class_basename($resourceClass);
        $name = Str::replaceLast('Resource', '', $shortName);
        return Str::headline($name);
    }

    /**
     * Get widget group (for categorization in UI)
     */
    protected function getWidgetGroup(string $widgetClass): string
    {
        $shortName = class_basename($widgetClass);

        if (Str::contains($shortName, ['Stock', 'Inventory', 'Product'])) {
            return 'inventory';
        }
        if (Str::contains($shortName, ['Order', 'Sales', 'Revenue'])) {
            return 'sales';
        }
        if (Str::contains($shortName, ['Customer'])) {
            return 'customers';
        }

        return 'general';
    }

    /**
     * Get resource group
     */
    protected function getResourceGroup(string $resourceClass): string
    {
        if (
            Str::contains($resourceClass, '\\Products\\') ||
            Str::contains($resourceClass, 'Category')
        ) {
            return 'catalog';
        }
        if (
            Str::contains($resourceClass, '\\Orders\\') ||
            Str::contains($resourceClass, 'Payment') ||
            Str::contains($resourceClass, 'Coupon')
        ) {
            return 'sales';
        }
        if (
            Str::contains($resourceClass, 'Stock') ||
            Str::contains($resourceClass, 'Warehouse')
        ) {
            return 'inventory';
        }

        return 'system';
    }

    /**
     * Get default widgets (for non-authenticated users)
     */
    protected function getDefaultWidgets(): array
    {
        return [
            \Filament\Widgets\AccountWidget::class,
        ];
    }
}
