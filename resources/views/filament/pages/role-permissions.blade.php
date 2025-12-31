<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Role Selector --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('admin.role_permissions.select_role') }}
                    </label>
                    <select wire:model.live="selectedRoleId"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        @foreach($this->getRoles() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                    {{ $this->getSelectedRoleName() }}
                </div>
            </div>
        </div>

        {{-- Widgets Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                    {{ __('admin.role_permissions.widgets') }}
                </h3>
                <div class="flex gap-2">
                    <button wire:click="enableAllWidgets"
                        class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:bg-green-900 dark:text-green-300">
                        {{ __('admin.role_permissions.enable_all') }}
                    </button>
                    <button wire:click="disableAllWidgets"
                        class="px-3 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-lg hover:bg-red-200 dark:bg-red-900 dark:text-red-300">
                        {{ __('admin.role_permissions.disable_all') }}
                    </button>
                </div>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($widgetPermissions as $widgetId => $widget)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white text-sm">
                                    {{ $widget['name'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $widget['group'] ?? 'general' }}
                                </div>
                            </div>
                            <button wire:click="toggleWidget({{ $widgetId }})"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $widget['is_visible'] ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}">
                                <span class="sr-only">Toggle</span>
                                <span
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $widget['is_visible'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Navigation Groups Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-bars-3 class="w-5 h-5" />
                    {{ __('admin.role_permissions.nav_groups') }}
                </h3>
                <button wire:click="enableAllNavGroups"
                    class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:bg-green-900 dark:text-green-300">
                    {{ __('admin.role_permissions.enable_all') }}
                </button>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($navGroupPermissions as $navGroupId => $navGroup)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white text-sm">
                                    {{ $navGroup['label_en'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $navGroup['label_ar'] }}
                                </div>
                            </div>
                            <button wire:click="toggleNavGroup({{ $navGroupId }})"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $navGroup['is_visible'] ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600' }}">
                                <span class="sr-only">Toggle</span>
                                <span
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $navGroup['is_visible'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Resources Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-rectangle-stack class="w-5 h-5" />
                    {{ __('admin.role_permissions.resources') }}
                </h3>
                <button wire:click="grantFullResourceAccess"
                    class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 dark:bg-green-900 dark:text-green-300">
                    {{ __('admin.role_permissions.grant_full_access') }}
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('admin.role_permissions.resource_name') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('admin.role_permissions.view') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('admin.role_permissions.create') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('admin.role_permissions.edit') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('admin.role_permissions.delete') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($resourcePermissions as $resourceId => $resource)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white text-sm">
                                        {{ $resource['name'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $resource['navigation_group'] ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="updateResourcePermission({{ $resourceId }}, 'can_view')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $resource['can_view'] ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' }}">
                                        @if($resource['can_view'])
                                            <x-heroicon-s-check class="w-5 h-5" />
                                        @else
                                            <x-heroicon-s-x-mark class="w-5 h-5" />
                                        @endif
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="updateResourcePermission({{ $resourceId }}, 'can_create')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $resource['can_create'] ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' }}"
                                        @if(!$resource['can_view']) disabled @endif>
                                        @if($resource['can_create'])
                                            <x-heroicon-s-check class="w-5 h-5" />
                                        @else
                                            <x-heroicon-s-x-mark class="w-5 h-5" />
                                        @endif
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="updateResourcePermission({{ $resourceId }}, 'can_edit')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $resource['can_edit'] ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' }}"
                                        @if(!$resource['can_view']) disabled @endif>
                                        @if($resource['can_edit'])
                                            <x-heroicon-s-check class="w-5 h-5" />
                                        @else
                                            <x-heroicon-s-x-mark class="w-5 h-5" />
                                        @endif
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="updateResourcePermission({{ $resourceId }}, 'can_delete')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $resource['can_delete'] ? 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' }}"
                                        @if(!$resource['can_view']) disabled @endif>
                                        @if($resource['can_delete'])
                                            <x-heroicon-s-check class="w-5 h-5" />
                                        @else
                                            <x-heroicon-s-x-mark class="w-5 h-5" />
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>