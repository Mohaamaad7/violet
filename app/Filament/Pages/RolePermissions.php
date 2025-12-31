<?php

namespace App\Filament\Pages;

use App\Models\NavigationGroupConfiguration;
use App\Models\ResourceConfiguration;
use App\Models\Role;
use App\Models\RoleNavigationGroup;
use App\Models\RoleResourceAccess;
use App\Models\RoleWidgetDefault;
use App\Models\WidgetConfiguration;
use App\Services\DashboardConfigurationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

class RolePermissions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected string $view = 'filament.pages.role-permissions';

    protected static ?int $navigationSort = 103;

    // Form state
    public ?int $selectedRoleId = null;

    // Widget permissions
    public array $widgetPermissions = [];

    // Resource permissions
    public array $resourcePermissions = [];

    // Navigation group permissions
    public array $navGroupPermissions = [];

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

    protected function loadPermissions(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        // Load widget permissions
        $this->widgetPermissions = [];
        $widgets = WidgetConfiguration::all();
        foreach ($widgets as $widget) {
            $roleDefault = RoleWidgetDefault::where('role_id', $this->selectedRoleId)
                ->where('widget_configuration_id', $widget->id)
                ->first();

            $this->widgetPermissions[$widget->id] = [
                'name' => $widget->widget_name,
                'group' => $widget->widget_group,
                'is_visible' => $roleDefault?->is_visible ?? true,
            ];
        }

        // Load resource permissions
        $this->resourcePermissions = [];
        $resources = ResourceConfiguration::all();
        foreach ($resources as $resource) {
            $roleAccess = RoleResourceAccess::where('role_id', $this->selectedRoleId)
                ->where('resource_configuration_id', $resource->id)
                ->first();

            $this->resourcePermissions[$resource->id] = [
                'name' => $resource->resource_name,
                'navigation_group' => $resource->navigation_group,
                'can_view' => $roleAccess?->can_view ?? true,
                'can_create' => $roleAccess?->can_create ?? false,
                'can_edit' => $roleAccess?->can_edit ?? false,
                'can_delete' => $roleAccess?->can_delete ?? false,
                'is_visible_in_navigation' => $roleAccess?->is_visible_in_navigation ?? true,
            ];
        }

        // Load navigation group permissions
        $this->navGroupPermissions = [];
        $navGroups = NavigationGroupConfiguration::all();
        foreach ($navGroups as $navGroup) {
            $roleNavGroup = RoleNavigationGroup::where('role_id', $this->selectedRoleId)
                ->where('navigation_group_id', $navGroup->id)
                ->first();

            $this->navGroupPermissions[$navGroup->id] = [
                'key' => $navGroup->group_key,
                'label_ar' => $navGroup->group_label_ar,
                'label_en' => $navGroup->group_label_en,
                'is_visible' => $roleNavGroup?->is_visible ?? true,
            ];
        }
    }

    public function toggleWidget(int $widgetId): void
    {
        if (!$this->selectedRoleId)
            return;

        $current = $this->widgetPermissions[$widgetId]['is_visible'] ?? true;
        $newValue = !$current;

        RoleWidgetDefault::updateOrCreate(
            [
                'role_id' => $this->selectedRoleId,
                'widget_configuration_id' => $widgetId,
            ],
            [
                'is_visible' => $newValue,
                'order_position' => WidgetConfiguration::find($widgetId)?->default_order ?? 0,
                'column_span' => WidgetConfiguration::find($widgetId)?->default_column_span ?? 1,
            ]
        );

        $this->widgetPermissions[$widgetId]['is_visible'] = $newValue;
        $this->clearCache();

        Notification::make()
            ->title($newValue ? __('admin.role_permissions.widget_enabled') : __('admin.role_permissions.widget_disabled'))
            ->success()
            ->send();
    }

    public function toggleNavGroup(int $navGroupId): void
    {
        if (!$this->selectedRoleId)
            return;

        $current = $this->navGroupPermissions[$navGroupId]['is_visible'] ?? true;
        $newValue = !$current;

        RoleNavigationGroup::updateOrCreate(
            [
                'role_id' => $this->selectedRoleId,
                'navigation_group_id' => $navGroupId,
            ],
            [
                'is_visible' => $newValue,
                'order_position' => NavigationGroupConfiguration::find($navGroupId)?->default_order ?? 0,
            ]
        );

        $this->navGroupPermissions[$navGroupId]['is_visible'] = $newValue;
        $this->clearCache();

        Notification::make()
            ->title($newValue ? __('admin.role_permissions.nav_group_enabled') : __('admin.role_permissions.nav_group_disabled'))
            ->success()
            ->send();
    }

    public function updateResourcePermission(int $resourceId, string $permission): void
    {
        if (!$this->selectedRoleId)
            return;

        $current = $this->resourcePermissions[$resourceId][$permission] ?? false;
        $newValue = !$current;

        $updateData = [$permission => $newValue];

        // If enabling view, also enable navigation visibility
        if ($permission === 'can_view' && $newValue) {
            $updateData['is_visible_in_navigation'] = true;
            $this->resourcePermissions[$resourceId]['is_visible_in_navigation'] = true;
        }

        // If disabling view, also disable everything else
        if ($permission === 'can_view' && !$newValue) {
            $updateData['can_create'] = false;
            $updateData['can_edit'] = false;
            $updateData['can_delete'] = false;
            $updateData['is_visible_in_navigation'] = false;
            $this->resourcePermissions[$resourceId]['can_create'] = false;
            $this->resourcePermissions[$resourceId]['can_edit'] = false;
            $this->resourcePermissions[$resourceId]['can_delete'] = false;
            $this->resourcePermissions[$resourceId]['is_visible_in_navigation'] = false;
        }

        RoleResourceAccess::updateOrCreate(
            [
                'role_id' => $this->selectedRoleId,
                'resource_configuration_id' => $resourceId,
            ],
            array_merge([
                'navigation_sort' => ResourceConfiguration::find($resourceId)?->default_navigation_sort ?? 0,
            ], $updateData)
        );

        $this->resourcePermissions[$resourceId][$permission] = $newValue;
        $this->clearCache();

        Notification::make()
            ->title(__('admin.role_permissions.permission_updated'))
            ->success()
            ->send();
    }

    public function enableAllWidgets(): void
    {
        if (!$this->selectedRoleId)
            return;

        foreach ($this->widgetPermissions as $widgetId => $data) {
            RoleWidgetDefault::updateOrCreate(
                [
                    'role_id' => $this->selectedRoleId,
                    'widget_configuration_id' => $widgetId,
                ],
                ['is_visible' => true]
            );
            $this->widgetPermissions[$widgetId]['is_visible'] = true;
        }

        $this->clearCache();
        Notification::make()->title(__('admin.role_permissions.all_widgets_enabled'))->success()->send();
    }

    public function disableAllWidgets(): void
    {
        if (!$this->selectedRoleId)
            return;

        foreach ($this->widgetPermissions as $widgetId => $data) {
            RoleWidgetDefault::updateOrCreate(
                [
                    'role_id' => $this->selectedRoleId,
                    'widget_configuration_id' => $widgetId,
                ],
                ['is_visible' => false]
            );
            $this->widgetPermissions[$widgetId]['is_visible'] = false;
        }

        $this->clearCache();
        Notification::make()->title(__('admin.role_permissions.all_widgets_disabled'))->success()->send();
    }

    public function enableAllNavGroups(): void
    {
        if (!$this->selectedRoleId)
            return;

        foreach ($this->navGroupPermissions as $navGroupId => $data) {
            RoleNavigationGroup::updateOrCreate(
                [
                    'role_id' => $this->selectedRoleId,
                    'navigation_group_id' => $navGroupId,
                ],
                ['is_visible' => true]
            );
            $this->navGroupPermissions[$navGroupId]['is_visible'] = true;
        }

        $this->clearCache();
        Notification::make()->title(__('admin.role_permissions.all_nav_groups_enabled'))->success()->send();
    }

    public function grantFullResourceAccess(): void
    {
        if (!$this->selectedRoleId)
            return;

        foreach ($this->resourcePermissions as $resourceId => $data) {
            RoleResourceAccess::updateOrCreate(
                [
                    'role_id' => $this->selectedRoleId,
                    'resource_configuration_id' => $resourceId,
                ],
                [
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'is_visible_in_navigation' => true,
                ]
            );
            $this->resourcePermissions[$resourceId]['can_view'] = true;
            $this->resourcePermissions[$resourceId]['can_create'] = true;
            $this->resourcePermissions[$resourceId]['can_edit'] = true;
            $this->resourcePermissions[$resourceId]['can_delete'] = true;
            $this->resourcePermissions[$resourceId]['is_visible_in_navigation'] = true;
        }

        $this->clearCache();
        Notification::make()->title(__('admin.role_permissions.full_access_granted'))->success()->send();
    }

    protected function clearCache(): void
    {
        Cache::flush();
    }

    public function getRoles(): array
    {
        return Role::pluck('name', 'id')->toArray();
    }

    public function getSelectedRoleName(): string
    {
        return Role::find($this->selectedRoleId)?->name ?? '';
    }
}
