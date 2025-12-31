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
 * Role Permissions Page - Zero-Config Approach
 * 
 * This page allows admins to manage widget/resource visibility per role.
 * Widgets and resources are discovered from CODE at runtime (no DB registration needed).
 * Database only stores EXCEPTIONS (hidden items), not visible items.
 * 
 * Default: Everything is VISIBLE. Only store what's hidden.
 */
class RolePermissions extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected string $view = 'filament.pages.role-permissions';

    protected static ?int $navigationSort = 103;

    // Form state
    public ?int $selectedRoleId = null;

    // Widget permissions (discovered from code)
    public array $widgets = [];

    // Resource permissions (discovered from code)
    public array $resources = [];

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

    /**
     * Load permissions using runtime discovery
     * No database registration needed - discovers from code!
     */
    protected function loadPermissions(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        $this->service = app(DashboardConfigurationService::class);

        // Discover widgets from CODE and get their status for this role
        $this->widgets = $this->service->getWidgetsWithStatusForRole($this->selectedRoleId);

        // Discover resources from CODE and get their status for this role
        $this->resources = $this->service->getResourcesWithStatusForRole($this->selectedRoleId);
    }

    /**
     * Toggle a widget's visibility for the selected role
     * Uses widget_class directly (no configuration ID needed)
     */
    public function toggleWidget(string $widgetClass): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        // Find current status
        $currentlyVisible = true;
        foreach ($this->widgets as &$widget) {
            if ($widget['class'] === $widgetClass) {
                $currentlyVisible = $widget['is_visible'];
                $widget['is_visible'] = !$currentlyVisible;
                break;
            }
        }

        $newValue = !$currentlyVisible;

        // Update database
        $this->service->setWidgetVisibility($this->selectedRoleId, $widgetClass, $newValue);

        Notification::make()
            ->title($newValue ? __('admin.role_permissions.widget_enabled') : __('admin.role_permissions.widget_disabled'))
            ->success()
            ->send();
    }

    /**
     * Toggle a resource permission for the selected role
     */
    public function toggleResourcePermission(string $resourceClass, string $permission): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        // Find current status and toggle
        $newPermissions = [];
        foreach ($this->resources as &$resource) {
            if ($resource['class'] === $resourceClass) {
                $currentValue = $resource[$permission];
                $resource[$permission] = !$currentValue;

                // Build permissions array
                $newPermissions = [
                    'can_view' => $resource['can_view'],
                    'can_create' => $resource['can_create'],
                    'can_edit' => $resource['can_edit'],
                    'can_delete' => $resource['can_delete'],
                ];

                // If disabling view, disable everything
                if ($permission === 'can_view' && $resource[$permission] === false) {
                    $resource['can_create'] = false;
                    $resource['can_edit'] = false;
                    $resource['can_delete'] = false;
                    $newPermissions['can_create'] = false;
                    $newPermissions['can_edit'] = false;
                    $newPermissions['can_delete'] = false;
                }

                break;
            }
        }

        // Update database
        $this->service->setResourceAccess($this->selectedRoleId, $resourceClass, $newPermissions);

        Notification::make()
            ->title(__('admin.role_permissions.permission_updated'))
            ->success()
            ->send();
    }

    /**
     * Enable all widgets for the selected role
     * Simply delete all "hide" overrides - back to default (visible)
     */
    public function enableAllWidgets(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        // Delete all hide overrides for this role
        RoleWidgetDefault::where('role_id', $this->selectedRoleId)->delete();

        // Update UI state
        foreach ($this->widgets as &$widget) {
            $widget['is_visible'] = true;
            $widget['has_override'] = false;
        }

        Cache::flush();

        Notification::make()
            ->title(__('admin.role_permissions.all_widgets_enabled'))
            ->success()
            ->send();
    }

    /**
     * Disable all widgets for the selected role
     */
    public function disableAllWidgets(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        foreach ($this->widgets as &$widget) {
            $this->service->setWidgetVisibility($this->selectedRoleId, $widget['class'], false);
            $widget['is_visible'] = false;
            $widget['has_override'] = true;
        }

        Notification::make()
            ->title(__('admin.role_permissions.all_widgets_disabled'))
            ->success()
            ->send();
    }

    /**
     * Grant full resource access for the selected role
     * Simply delete all restriction overrides - back to default (full access)
     */
    public function grantFullResourceAccess(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        // Delete all restriction overrides for this role
        RoleResourceAccess::where('role_id', $this->selectedRoleId)->delete();

        // Update UI state
        foreach ($this->resources as &$resource) {
            $resource['can_view'] = true;
            $resource['can_create'] = true;
            $resource['can_edit'] = true;
            $resource['can_delete'] = true;
            $resource['has_override'] = false;
        }

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
}
