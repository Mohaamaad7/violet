<?php

namespace App\Console\Commands;

use App\Models\NavigationGroupConfiguration;
use App\Models\ResourceConfiguration;
use App\Models\Role;
use App\Models\RoleNavigationGroup;
use App\Models\RoleResourceAccess;
use App\Models\RoleWidgetDefault;
use App\Models\WidgetConfiguration;
use Illuminate\Console\Command;

/**
 * Sync role configurations with discovered widgets/resources
 */
class DashboardSyncRoles extends Command
{
    protected $signature = 'dashboard:sync-roles 
                            {--role= : Specific role name to sync}
                            {--super-admin-all : Give super-admin access to everything}';

    protected $description = 'Sync role configurations with available widgets, resources, and navigation groups';

    public function handle(): int
    {
        $this->info('🔄 Syncing role configurations...');
        $this->newLine();

        $roleName = $this->option('role');
        $roles = $roleName
            ? Role::where('name', $roleName)->get()
            : Role::all();

        if ($roles->isEmpty()) {
            $this->error("No roles found" . ($roleName ? " with name '{$roleName}'" : ""));
            return Command::FAILURE;
        }

        foreach ($roles as $role) {
            $this->syncRoleWidgets($role);
            $this->syncRoleResources($role);
            $this->syncRoleNavigationGroups($role);

            $this->info("   ✅ Synced role: {$role->name}");
        }

        $this->newLine();
        $this->info('✨ Role sync complete!');

        return Command::SUCCESS;
    }

    protected function syncRoleWidgets(Role $role): void
    {
        $isSuperAdmin = $role->name === 'super-admin' && $this->option('super-admin-all');

        $widgets = WidgetConfiguration::active()->get();

        foreach ($widgets as $widget) {
            RoleWidgetDefault::updateOrCreate(
                [
                    'role_id' => $role->id,
                    'widget_configuration_id' => $widget->id,
                ],
                [
                    'is_visible' => $isSuperAdmin ? true : $this->shouldWidgetBeVisible($role, $widget),
                    'order_position' => $widget->default_order,
                    'column_span' => $widget->default_column_span,
                ]
            );
        }
    }

    protected function syncRoleResources(Role $role): void
    {
        $isSuperAdmin = $role->name === 'super-admin' && $this->option('super-admin-all');

        $resources = ResourceConfiguration::active()->get();

        foreach ($resources as $resource) {
            $permissions = $isSuperAdmin
                ? ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true]
                : $this->getResourcePermissionsForRole($role, $resource);

            RoleResourceAccess::updateOrCreate(
                [
                    'role_id' => $role->id,
                    'resource_configuration_id' => $resource->id,
                ],
                array_merge($permissions, [
                    'is_visible_in_navigation' => $permissions['can_view'],
                    'navigation_sort' => $resource->default_navigation_sort,
                ])
            );
        }
    }

    protected function syncRoleNavigationGroups(Role $role): void
    {
        $isSuperAdmin = $role->name === 'super-admin' && $this->option('super-admin-all');

        $groups = NavigationGroupConfiguration::active()->get();

        foreach ($groups as $group) {
            RoleNavigationGroup::updateOrCreate(
                [
                    'role_id' => $role->id,
                    'navigation_group_id' => $group->id,
                ],
                [
                    'is_visible' => $isSuperAdmin ? true : $this->shouldGroupBeVisible($role, $group),
                    'order_position' => $group->default_order,
                ]
            );
        }
    }

    /**
     * Determine if a widget should be visible based on role and widget group
     */
    protected function shouldWidgetBeVisible(Role $role, WidgetConfiguration $widget): bool
    {
        $roleGroup = strtolower($role->name);
        $widgetGroup = strtolower($widget->widget_group ?? 'general');

        // Define role-widget group mappings
        $roleWidgetMap = [
            'admin' => ['sales', 'inventory', 'customers', 'general'],
            'manager' => ['sales', 'inventory', 'customers', 'general'],
            'sales' => ['sales', 'customers', 'general'],
            'accountant' => ['sales', 'general'],
            'content-manager' => ['general'],
            'delivery' => ['sales', 'general'],
        ];

        $allowedGroups = $roleWidgetMap[$roleGroup] ?? ['general'];

        return in_array($widgetGroup, $allowedGroups);
    }

    /**
     * Get resource permissions based on role
     */
    protected function getResourcePermissionsForRole(Role $role, ResourceConfiguration $resource): array
    {
        // Default: view only
        $permissions = [
            'can_view' => true,
            'can_create' => false,
            'can_edit' => false,
            'can_delete' => false,
        ];

        // Admin and Manager get full access
        if (in_array($role->name, ['admin', 'manager'])) {
            $permissions['can_create'] = true;
            $permissions['can_edit'] = true;
            $permissions['can_delete'] = true;
        }

        return $permissions;
    }

    /**
     * Determine if a navigation group should be visible for a role
     */
    protected function shouldGroupBeVisible(Role $role, NavigationGroupConfiguration $group): bool
    {
        $roleGroup = strtolower($role->name);
        $navGroup = strtolower($group->group_key);

        // Define role-navigation group mappings
        $roleNavMap = [
            'admin' => ['catalog', 'sales', 'inventory', 'customers', 'content', 'geography', 'settings', 'system'],
            'manager' => ['catalog', 'sales', 'inventory', 'customers', 'content', 'geography'],
            'sales' => ['catalog', 'sales', 'customers'],
            'accountant' => ['sales'],
            'content-manager' => ['catalog', 'content'],
            'delivery' => ['sales'],
        ];

        $allowedGroups = $roleNavMap[$roleGroup] ?? [];

        return in_array($navGroup, $allowedGroups);
    }
}
