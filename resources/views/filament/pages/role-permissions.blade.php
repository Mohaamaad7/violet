<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Role Selector --}}
        <x-filament::section>
            <x-slot name="heading">
                {{ __('admin.role_permissions.select_role') }}
            </x-slot>
            
            <div class="flex items-center gap-4">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="selectedRoleId">
                        @foreach($this->getRoles() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                
                <x-filament::badge color="primary" size="lg">
                    {{ $this->getSelectedRoleName() }}
                </x-filament::badge>
            </div>
        </x-filament::section>

        {{-- Widgets Section --}}
        <x-filament::section collapsible>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                    {{ __('admin.role_permissions.widgets') }}
                </div>
            </x-slot>
            
            <x-slot name="headerEnd">
                <div class="flex gap-2">
                    <x-filament::button size="xs" color="success" wire:click="enableAllWidgets">
                        {{ __('admin.role_permissions.enable_all') }}
                    </x-filament::button>
                    <x-filament::button size="xs" color="danger" wire:click="disableAllWidgets">
                        {{ __('admin.role_permissions.disable_all') }}
                    </x-filament::button>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($widgetPermissions as $widgetId => $widget)
                    <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ $widget['name'] }}
                            </div>
                            <x-filament::badge size="sm" color="{{ match($widget['group'] ?? 'general') {
                                'sales' => 'success',
                                'inventory' => 'warning',
                                'customers' => 'info',
                                default => 'gray'
                            } }}">
                                {{ $widget['group'] ?? 'general' }}
                            </x-filament::badge>
                        </div>
                        <div class="ms-4">
                            @if($widget['is_visible'])
                                <x-filament::icon-button 
                                    icon="heroicon-s-check-circle" 
                                    color="success"
                                    wire:click="toggleWidget({{ $widgetId }})"
                                    tooltip="{{ __('admin.role_permissions.widget_enabled') }}"
                                />
                            @else
                                <x-filament::icon-button 
                                    icon="heroicon-s-x-circle" 
                                    color="danger"
                                    wire:click="toggleWidget({{ $widgetId }})"
                                    tooltip="{{ __('admin.role_permissions.widget_disabled') }}"
                                />
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- Navigation Groups Section --}}
        <x-filament::section collapsible>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-bars-3 class="w-5 h-5" />
                    {{ __('admin.role_permissions.nav_groups') }}
                </div>
            </x-slot>
            
            <x-slot name="headerEnd">
                <x-filament::button size="xs" color="success" wire:click="enableAllNavGroups">
                    {{ __('admin.role_permissions.enable_all') }}
                </x-filament::button>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($navGroupPermissions as $navGroupId => $navGroup)
                    <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ $navGroup['label_en'] }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $navGroup['label_ar'] }}
                            </div>
                        </div>
                        <div class="ms-4">
                            @if($navGroup['is_visible'])
                                <x-filament::icon-button 
                                    icon="heroicon-s-check-circle" 
                                    color="success"
                                    wire:click="toggleNavGroup({{ $navGroupId }})"
                                />
                            @else
                                <x-filament::icon-button 
                                    icon="heroicon-s-x-circle" 
                                    color="danger"
                                    wire:click="toggleNavGroup({{ $navGroupId }})"
                                />
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- Resources Section --}}
        <x-filament::section collapsible>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-rectangle-stack class="w-5 h-5" />
                    {{ __('admin.role_permissions.resources') }}
                </div>
            </x-slot>
            
            <x-slot name="headerEnd">
                <x-filament::button size="xs" color="success" wire:click="grantFullResourceAccess">
                    {{ __('admin.role_permissions.grant_full_access') }}
                </x-filament::button>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-start p-3 font-semibold text-gray-900 dark:text-white">
                                {{ __('admin.role_permissions.resource_name') }}
                            </th>
                            <th class="p-3 font-semibold text-gray-900 dark:text-white text-center w-20">
                                {{ __('admin.role_permissions.view') }}
                            </th>
                            <th class="p-3 font-semibold text-gray-900 dark:text-white text-center w-20">
                                {{ __('admin.role_permissions.create') }}
                            </th>
                            <th class="p-3 font-semibold text-gray-900 dark:text-white text-center w-20">
                                {{ __('admin.role_permissions.edit') }}
                            </th>
                            <th class="p-3 font-semibold text-gray-900 dark:text-white text-center w-20">
                                {{ __('admin.role_permissions.delete') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($resourcePermissions as $resourceId => $resource)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="p-3">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $resource['name'] }}
                                    </div>
                                    @if($resource['navigation_group'])
                                        <x-filament::badge size="sm" color="gray">
                                            {{ $resource['navigation_group'] }}
                                        </x-filament::badge>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <x-filament::icon-button 
                                        :icon="$resource['can_view'] ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle'"
                                        :color="$resource['can_view'] ? 'success' : 'gray'"
                                        size="sm"
                                        wire:click="updateResourcePermission({{ $resourceId }}, 'can_view')"
                                    />
                                </td>
                                <td class="p-3 text-center">
                                    <x-filament::icon-button 
                                        :icon="$resource['can_create'] ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle'"
                                        :color="$resource['can_create'] ? 'success' : 'gray'"
                                        size="sm"
                                        wire:click="updateResourcePermission({{ $resourceId }}, 'can_create')"
                                    />
                                </td>
                                <td class="p-3 text-center">
                                    <x-filament::icon-button 
                                        :icon="$resource['can_edit'] ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle'"
                                        :color="$resource['can_edit'] ? 'success' : 'gray'"
                                        size="sm"
                                        wire:click="updateResourcePermission({{ $resourceId }}, 'can_edit')"
                                    />
                                </td>
                                <td class="p-3 text-center">
                                    <x-filament::icon-button 
                                        :icon="$resource['can_delete'] ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle'"
                                        :color="$resource['can_delete'] ? 'danger' : 'gray'"
                                        size="sm"
                                        wire:click="updateResourcePermission({{ $resourceId }}, 'can_delete')"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>