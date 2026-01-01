<?php

namespace App\Filament\Pages;

use App\Models\Role;
use App\Models\RoleResourceAccess;
use App\Models\RoleWidgetDefault;
use App\Services\DashboardConfigurationService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

/**
 * Role Permissions Page - Zero-Config with Smart Grouping
 * 
 * Features:
 * - Filter by group (sales, inventory, etc.)
 * - Grouped display with cards
 * - Localized names
 * - Bulk actions per group
 */
class RolePermissions extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected string $view = 'filament.pages.role-permissions';

    protected static ?int $navigationSort = 103;

    // Form state
    public ?int $selectedRoleId = null;
    public string $selectedGroup = 'all';

    // Data
    public array $widgetsGrouped = [];
    public array $resourcesGrouped = [];
    public array $availableGroups = [];

    protected DashboardConfigurationService $service;

    public function boot(): void
    {
        $this->service = app(DashboardConfigurationService::class);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.role_permissions.title');
    }

    public function getTitle(): string
    {
        return __('admin.role_permissions.title');
    }

    public function mount(): void
    {
        $this->service = app(DashboardConfigurationService::class);

        // Load available groups
        $this->availableGroups = $this->service->getAvailableGroups();

        // Default to first role
        $firstRole = Role::first();
        if ($firstRole) {
            $this->selectedRoleId = $firstRole->id;
            $this->loadPermissions();
        }
    }

    public function updatedSelectedRoleId(): void
    {
        $this->loadPermissions();
    }

    public function updatedSelectedGroup(): void
    {
        // Just trigger re-render, filtering is done in view
    }

    /**
     * Load permissions using runtime discovery with grouping
     */
    protected function loadPermissions(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        $this->service = app(DashboardConfigurationService::class);

        // Get widgets grouped by category
        $this->widgetsGrouped = $this->service->getWidgetsGroupedForRole($this->selectedRoleId);

        // Get resources grouped by category
        $this->resourcesGrouped = $this->service->getResourcesGroupedForRole($this->selectedRoleId);
    }

    /**
     * Toggle a widget's visibility
     */
    public function toggleWidget(string $widgetClass): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        // Find current status
        $currentlyVisible = true;
        foreach ($this->widgetsGrouped as $group => &$groupData) {
            foreach ($groupData['items'] as &$widget) {
                if ($widget['class'] === $widgetClass) {
                    $currentlyVisible = $widget['is_visible'];
                    $widget['is_visible'] = !$currentlyVisible;
                    $widget['has_override'] = !$currentlyVisible ? false : true;
                    break 2;
                }
            }
        }

        $newValue = !$currentlyVisible;
        $this->service->setWidgetVisibility($this->selectedRoleId, $widgetClass, $newValue);

        Notification::make()
            ->title($newValue ? __('admin.role_permissions.widget_enabled') : __('admin.role_permissions.widget_disabled'))
            ->success()
            ->send();
    }

    /**
     * Toggle a resource permission
     */
    public function toggleResourcePermission(string $resourceClass, string $permission): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        // Find and toggle
        $newPermissions = [];
        foreach ($this->resourcesGrouped as $group => &$groupData) {
            foreach ($groupData['items'] as &$resource) {
                if ($resource['class'] === $resourceClass) {
                    $resource[$permission] = !$resource[$permission];

                    $newPermissions = [
                        'can_view' => $resource['can_view'],
                        'can_create' => $resource['can_create'],
                        'can_edit' => $resource['can_edit'],
                        'can_delete' => $resource['can_delete'],
                    ];

                    // If disabling view, disable everything
                    if ($permission === 'can_view' && !$resource['can_view']) {
                        $resource['can_create'] = false;
                        $resource['can_edit'] = false;
                        $resource['can_delete'] = false;
                        $newPermissions['can_create'] = false;
                        $newPermissions['can_edit'] = false;
                        $newPermissions['can_delete'] = false;
                    }

                    break 2;
                }
            }
        }

        $this->service->setResourceAccess($this->selectedRoleId, $resourceClass, $newPermissions);

        Notification::make()
            ->title(__('admin.role_permissions.permission_updated'))
            ->success()
            ->send();
    }

    /**
     * Enable all widgets in a specific group
     */
    public function enableGroupWidgets(string $group): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        $this->service->setGroupWidgetsVisibility($this->selectedRoleId, $group, true);
        $this->loadPermissions();

        Notification::make()
            ->title(__('admin.role_permissions.group_widgets_enabled'))
            ->success()
            ->send();
    }

    /**
     * Disable all widgets in a specific group
     */
    public function disableGroupWidgets(string $group): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        $this->service->setGroupWidgetsVisibility($this->selectedRoleId, $group, false);
        $this->loadPermissions();

        Notification::make()
            ->title(__('admin.role_permissions.group_widgets_disabled'))
            ->success()
            ->send();
    }

    /**
     * Grant full access to resources in a group
     */
    public function grantGroupResourceAccess(string $group): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        $this->service->setGroupResourcesAccess($this->selectedRoleId, $group, true);
        $this->loadPermissions();

        Notification::make()
            ->title(__('admin.role_permissions.group_access_granted'))
            ->success()
            ->send();
    }

    /**
     * Revoke all access to resources in a group
     */
    public function revokeGroupResourceAccess(string $group): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        $this->service->setGroupResourcesAccess($this->selectedRoleId, $group, false);
        $this->loadPermissions();

        Notification::make()
            ->title(__('admin.role_permissions.group_access_revoked'))
            ->success()
            ->send();
    }

    /**
     * Enable all widgets (all groups)
     */
    public function enableAllWidgets(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        RoleWidgetDefault::where('role_id', $this->selectedRoleId)->delete();
        $this->loadPermissions();
        Cache::flush();

        Notification::make()
            ->title(__('admin.role_permissions.all_widgets_enabled'))
            ->success()
            ->send();
    }

    /**
     * Grant full resource access (all groups)
     */
    public function grantFullResourceAccess(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        RoleResourceAccess::where('role_id', $this->selectedRoleId)->delete();
        $this->loadPermissions();
        Cache::flush();

        Notification::make()
            ->title(__('admin.role_permissions.full_access_granted'))
            ->success()
            ->send();
    }

    /**
     * Get all roles for the select dropdown
     */
    public function getRoles(): array
    {
        return Role::pluck('name', 'id')->toArray();
    }

    /**
     * Get the selected role name for display
     */
    public function getSelectedRoleName(): string
    {
        if (!$this->selectedRoleId) {
            return '';
        }
        return Role::find($this->selectedRoleId)?->name ?? '';
    }

    /**
     * Get filtered widgets based on selected group
     */
    public function getFilteredWidgets(): array
    {
        if ($this->selectedGroup === 'all') {
            return $this->widgetsGrouped;
        }

        return array_filter($this->widgetsGrouped, fn($group) => $group['key'] === $this->selectedGroup);
    }

    /**
     * Get filtered resources based on selected group
     */
    public function getFilteredResources(): array
    {
        if ($this->selectedGroup === 'all') {
            return $this->resourcesGrouped;
        }

        return array_filter($this->resourcesGrouped, fn($group) => $group['key'] === $this->selectedGroup);
    }

    /**
     * Get total counts for display
     */
    public function getTotalWidgetsCount(): int
    {
        $total = 0;
        foreach ($this->widgetsGrouped as $group) {
            $total += count($group['items']);
        }
        return $total;
    }

    public function getTotalResourcesCount(): int
    {
        $total = 0;
        foreach ($this->resourcesGrouped as $group) {
            $total += count($group['items']);
        }
        return $total;
    }
}
