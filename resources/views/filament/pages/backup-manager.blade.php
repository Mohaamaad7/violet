<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::section>
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                        <x-heroicon-o-archive-box class="w-6 h-6 text-primary-500" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.backup.total_backups') }}</p>
                        <p class="text-2xl font-bold">{{ count($this->getBackups()) }}</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-success-100 dark:bg-success-900/30 rounded-lg">
                        <x-heroicon-o-server-stack class="w-6 h-6 text-success-500" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.backup.total_size') }}</p>
                        <p class="text-2xl font-bold">{{ $this->getTotalBackupSize() }}</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-warning-100 dark:bg-warning-900/30 rounded-lg">
                        <x-heroicon-o-clock class="w-6 h-6 text-warning-500" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.backup.retention') }}</p>
                        <p class="text-2xl font-bold">
                            {{ config('backup.cleanup.default_strategy.keep_all_backups_for_days', 7) }}
                            {{ __('admin.backup.days') }}</p>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Create New Backup --}}
        <x-filament::section>
            <x-slot name="heading">
                {{ __('admin.backup.create_new') }}
            </x-slot>

            <div class="flex flex-wrap items-center gap-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="includeDatabase"
                        class="rounded border-gray-300 dark:border-gray-600">
                    <span>{{ __('admin.backup.include_database') }}</span>
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="includeFiles"
                        class="rounded border-gray-300 dark:border-gray-600">
                    <span>{{ __('admin.backup.include_files') }}</span>
                </label>

                <x-filament::button wire:click="createBackup" wire:loading.attr="disabled" icon="heroicon-o-plus">
                    <span wire:loading.remove wire:target="createBackup">{{ __('admin.backup.create') }}</span>
                    <span wire:loading wire:target="createBackup">{{ __('admin.backup.creating') }}...</span>
                </x-filament::button>

                <x-filament::button wire:click="runCleanup" wire:loading.attr="disabled" color="gray"
                    icon="heroicon-o-trash">
                    {{ __('admin.backup.cleanup') }}
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Existing Backups List --}}
        <x-filament::section>
            <x-slot name="heading">
                {{ __('admin.backup.existing_backups') }}
            </x-slot>

            @php $backups = $this->getBackups(); @endphp

            @if(empty($backups))
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-archive-box class="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p>{{ __('admin.backup.no_backups') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">
                                    {{ __('admin.backup.filename') }}</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">
                                    {{ __('admin.backup.type') }}</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">
                                    {{ __('admin.backup.size') }}</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">
                                    {{ __('admin.backup.created_at') }}</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">
                                    {{ __('admin.backup.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @foreach($backups as $backup)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-o-archive-box class="w-5 h-5 text-gray-400" />
                                            <span class="font-mono text-sm">{{ $backup['filename'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full font-medium
                                            @if($backup['type']['color'] === 'success') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($backup['type']['color'] === 'info') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                            @elseif($backup['type']['color'] === 'warning') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
                                            @else bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400
                                            @endif">
                                            {{ $backup['type']['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $backup['size'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $backup['created_at'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <x-filament::button wire:click="downloadBackup('{{ $backup['path'] }}')" size="sm"
                                                color="success" icon="heroicon-o-arrow-down-tray">
                                                {{ __('admin.backup.download') }}
                                            </x-filament::button>

                                            <x-filament::button wire:click="deleteBackup('{{ $backup['path'] }}')"
                                                wire:confirm="{{ __('admin.backup.confirm_delete') }}" size="sm" color="danger"
                                                icon="heroicon-o-trash">
                                                {{ __('admin.backup.delete') }}
                                            </x-filament::button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>