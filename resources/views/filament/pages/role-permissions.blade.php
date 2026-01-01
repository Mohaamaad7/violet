<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header: Role Selector + Group Filter --}}
        <x-filament::section>
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                {{-- Role Selector --}}
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('admin.role_permissions.select_role') }}
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="selectedRoleId" class="w-full">
                            @foreach($this->getRoles() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                {{-- Group Filter --}}
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('admin.role_permissions.filter_by_group') }}
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <x-filament::button size="sm" :color="$selectedGroup === 'all' ? 'primary' : 'gray'"
                            wire:click="$set('selectedGroup', 'all')">
                            {{ __('admin.role_permissions.all_groups') }}
                        </x-filament::button>

                        @foreach($availableGroups as $group)
                            @if(isset($widgetsGrouped[$group['key']]) || isset($resourcesGrouped[$group['key']]))
                                <x-filament::button size="sm" :color="$selectedGroup === $group['key'] ? 'primary' : 'gray'"
                                    wire:click="$set('selectedGroup', '{{ $group['key'] }}')">
                                    {{ $group['label'] }}
                                </x-filament::button>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Current Role Badge --}}
                <div class="flex items-center">
                    <x-filament::badge color="primary" size="lg">
                        {{ $this->getSelectedRoleName() }}
                    </x-filament::badge>
                </div>
            </div>
        </x-filament::section>

        {{-- Widgets Section - Grouped --}}
        <x-filament::section collapsible>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                    {{ __('admin.role_permissions.widgets') }}
                    <x-filament::badge size="sm" color="gray">
                        {{ $this->getTotalWidgetsCount() }}
                    </x-filament::badge>
                </div>
            </x-slot>

            <x-slot name="headerEnd">
                <x-filament::button size="xs" color="success" wire:click="enableAllWidgets">
                    {{ __('admin.role_permissions.enable_all') }}
                </x-filament::button>
            </x-slot>

            @php $filteredWidgets = $this->getFilteredWidgets(); @endphp

            @if(count($filteredWidgets) > 0)
                <div class="space-y-6">
                    @foreach($filteredWidgets as $groupKey => $group)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            {{-- Group Header --}}
                            <div
                                class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $group['label'] }}
                                    </h3>
                                    <x-filament::badge size="sm" color="info">
                                        {{ count($group['items']) }}
                                    </x-filament::badge>
                                </div>
                                <div class="flex gap-2">
                                    <x-filament::button size="xs" color="success"
                                        wire:click="enableGroupWidgets('{{ $groupKey }}')">
                                        {{ __('admin.role_permissions.enable_group') }}
                                    </x-filament::button>
                                    <x-filament::button size="xs" color="danger"
                                        wire:click="disableGroupWidgets('{{ $groupKey }}')">
                                        {{ __('admin.role_permissions.disable_group') }}
                                    </x-filament::button>
                                </div>
                            </div>

                            {{-- Group Items (Cards) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                                @foreach($group['items'] as $widget)
                                    <div
                                        class="flex items-center justify-between p-3 rounded-lg border {{ $widget['is_visible'] ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' }} transition-all duration-200">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-gray-900 dark:text-white truncate"
                                                title="{{ $widget['name'] }}">
                                                {{ $widget['name'] }}
                                            </div>
                                            @if($widget['has_override'])
                                                <x-filament::badge size="sm" color="warning">
                                                    Override
                                                </x-filament::badge>
                                            @endif
                                        </div>
                                        <div class="ms-2 flex-shrink-0">
                                            <button type="button" wire:click="toggleWidget('{{ addslashes($widget['class']) }}')"
                                                class="p-2 rounded-full transition-colors {{ $widget['is_visible'] ? 'text-green-600 hover:bg-green-100 dark:hover:bg-green-800' : 'text-red-600 hover:bg-red-100 dark:hover:bg-red-800' }}">
                                                @if($widget['is_visible'])
                                                    <x-heroicon-s-check-circle class="w-6 h-6" />
                                                @else
                                                    <x-heroicon-s-x-circle class="w-6 h-6" />
                                                @endif
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-squares-2x2 class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p>{{ __('admin.role_permissions.no_widgets_in_group') }}</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Resources Section - Grouped --}}
        <x-filament::section collapsible>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-rectangle-stack class="w-5 h-5" />
                    {{ __('admin.role_permissions.resources') }}
                    <x-filament::badge size="sm" color="gray">
                        {{ $this->getTotalResourcesCount() }}
                    </x-filament::badge>
                </div>
            </x-slot>

            <x-slot name="headerEnd">
                <x-filament::button size="xs" color="success" wire:click="grantFullResourceAccess">
                    {{ __('admin.role_permissions.grant_full_access') }}
                </x-filament::button>
            </x-slot>

            @php $filteredResources = $this->getFilteredResources(); @endphp

            @if(count($filteredResources) > 0)
                <div class="space-y-6">
                    @foreach($filteredResources as $groupKey => $group)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            {{-- Group Header --}}
                            <div
                                class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $group['label'] }}
                                    </h3>
                                    <x-filament::badge size="sm" color="info">
                                        {{ count($group['items']) }}
                                    </x-filament::badge>
                                </div>
                                <div class="flex gap-2">
                                    <x-filament::button size="xs" color="success"
                                        wire:click="grantGroupResourceAccess('{{ $groupKey }}')">
                                        {{ __('admin.role_permissions.grant_group_access') }}
                                    </x-filament::button>
                                    <x-filament::button size="xs" color="danger"
                                        wire:click="revokeGroupResourceAccess('{{ $groupKey }}')">
                                        {{ __('admin.role_permissions.revoke_group_access') }}
                                    </x-filament::button>
                                </div>
                            </div>

                            {{-- Resources Table --}}
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="text-start p-2 font-semibold text-gray-900 dark:text-white">
                                                {{ __('admin.role_permissions.resource_name') }}
                                            </th>
                                            <th class="p-2 font-semibold text-gray-900 dark:text-white text-center w-16">
                                                {{ __('admin.role_permissions.view') }}
                                            </th>
                                            <th class="p-2 font-semibold text-gray-900 dark:text-white text-center w-16">
                                                {{ __('admin.role_permissions.create') }}
                                            </th>
                                            <th class="p-2 font-semibold text-gray-900 dark:text-white text-center w-16">
                                                {{ __('admin.role_permissions.edit') }}
                                            </th>
                                            <th class="p-2 font-semibold text-gray-900 dark:text-white text-center w-16">
                                                {{ __('admin.role_permissions.delete') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($group['items'] as $resource)
                                            <tr
                                                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ !$resource['can_view'] ? 'opacity-60' : '' }}">
                                                <td class="p-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-medium text-gray-900 dark:text-white">
                                                            {{ $resource['name'] }}
                                                        </span>
                                                        @if($resource['has_override'])
                                                            <x-filament::badge size="sm" color="warning">
                                                                Override
                                                            </x-filament::badge>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button"
                                                        wire:click="toggleResourcePermission('{{ addslashes($resource['class']) }}', 'can_view')"
                                                        class="p-1 rounded {{ $resource['can_view'] ? 'text-green-600' : 'text-gray-400' }}">
                                                        @if($resource['can_view'])
                                                            <x-heroicon-s-check-circle class="w-5 h-5" />
                                                        @else
                                                            <x-heroicon-s-x-circle class="w-5 h-5" />
                                                        @endif
                                                    </button>
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button"
                                                        wire:click="toggleResourcePermission('{{ addslashes($resource['class']) }}', 'can_create')"
                                                        class="p-1 rounded {{ $resource['can_create'] ? 'text-green-600' : 'text-gray-400' }}">
                                                        @if($resource['can_create'])
                                                            <x-heroicon-s-check-circle class="w-5 h-5" />
                                                        @else
                                                            <x-heroicon-s-x-circle class="w-5 h-5" />
                                                        @endif
                                                    </button>
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button"
                                                        wire:click="toggleResourcePermission('{{ addslashes($resource['class']) }}', 'can_edit')"
                                                        class="p-1 rounded {{ $resource['can_edit'] ? 'text-green-600' : 'text-gray-400' }}">
                                                        @if($resource['can_edit'])
                                                            <x-heroicon-s-check-circle class="w-5 h-5" />
                                                        @else
                                                            <x-heroicon-s-x-circle class="w-5 h-5" />
                                                        @endif
                                                    </button>
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button"
                                                        wire:click="toggleResourcePermission('{{ addslashes($resource['class']) }}', 'can_delete')"
                                                        class="p-1 rounded {{ $resource['can_delete'] ? 'text-red-600' : 'text-gray-400' }}">
                                                        @if($resource['can_delete'])
                                                            <x-heroicon-s-check-circle class="w-5 h-5" />
                                                        @else
                                                            <x-heroicon-s-x-circle class="w-5 h-5" />
                                                        @endif
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-rectangle-stack class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p>{{ __('admin.role_permissions.no_resources_in_group') }}</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Info Box --}}
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400 space-y-2">
                <p class="flex items-center gap-2">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500" />
                    <strong>{{ __('admin.role_permissions.zero_config_info') }}</strong>
                </p>
                <ul class="list-disc list-inside ms-7 space-y-1">
                    <li>{{ __('admin.role_permissions.default_visible_info') }}</li>
                    <li>{{ __('admin.role_permissions.override_info') }}</li>
                    <li>{{ __('admin.role_permissions.auto_discover_info') }}</li>
                </ul>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>