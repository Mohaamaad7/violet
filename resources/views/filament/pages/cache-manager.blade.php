<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                {{ __('admin.cache.manage_cache') }}
            </x-slot>
            <x-slot name="description">
                {{ __('admin.cache.description') }}
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-filament::button wire:click="clearResponseCache" wire:confirm="{{ __('admin.cache.confirm_response') }}" color="warning" icon="heroicon-o-arrow-path" class="w-full">
                    {{ __('admin.cache.clear_response') }}
                </x-filament::button>

                <x-filament::button wire:click="clearAppCache" wire:confirm="{{ __('admin.cache.confirm_app') }}" color="warning" icon="heroicon-o-server-stack" class="w-full">
                    {{ __('admin.cache.clear_app') }}
                </x-filament::button>

                <x-filament::button wire:click="clearBladeCache" wire:confirm="{{ __('admin.cache.confirm_blade') }}" color="warning" icon="heroicon-o-code-bracket" class="w-full">
                    {{ __('admin.cache.clear_blade') }}
                </x-filament::button>

                <x-filament::button wire:click="clearAllCache" wire:confirm="{{ __('admin.cache.confirm_all') }}" color="danger" icon="heroicon-o-trash" class="w-full">
                    {{ __('admin.cache.clear_all') }}
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                {{ __('admin.cache.current_driver') }}
            </x-slot>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-filament::section>
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                            <x-heroicon-o-server-stack class="w-6 h-6 text-primary-500" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.cache.app_cache') }}</p>
                            <p class="text-lg font-bold">{{ config('cache.default') }}</p>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                            <x-heroicon-o-globe-alt class="w-6 h-6 text-primary-500" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.cache.response_cache') }}</p>
                            <p class="text-lg font-bold">{{ config('responsecache.cache_store') }}</p>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                            <x-heroicon-o-clock class="w-6 h-6 text-primary-500" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.cache.lifetime') }}</p>
                            <p class="text-lg font-bold">{{ config('responsecache.cache_lifetime_in_seconds') / 3600 }} {{ __('admin.cache.hours') }}</p>
                        </div>
                    </div>
                </x-filament::section>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
