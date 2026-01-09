<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Warning Section --}}
        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
            <div class="flex items-center gap-3">
                <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-red-500" />
                <div>
                    <h3 class="text-lg font-bold text-red-700 dark:text-red-300">
                        {{ __('admin.system_reset.warning_title') }}</h3>
                    <p class="text-red-600 dark:text-red-400">{{ __('admin.system_reset.warning_description') }}</p>
                </div>
            </div>
        </div>

        {{-- Main Form --}}
        <x-filament::section>
            <x-slot name="heading">
                {{ __('admin.system_reset.select_data_title') }}
            </x-slot>
            <x-slot name="description">
                {{ __('admin.system_reset.select_data_description') }}
            </x-slot>

            <form wire:submit="executeReset" class="space-y-6">
                {{ $this->form }}

                {{-- Execute Button --}}
                <div class="flex justify-end gap-4 pt-4 border-t dark:border-gray-700">
                    <x-filament::button type="submit" color="danger" size="lg" icon="heroicon-o-trash"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="executeReset">
                            {{ __('admin.system_reset.execute') }}
                        </span>
                        <span wire:loading wire:target="executeReset">
                            {{ __('admin.system_reset.in_progress') }}...
                        </span>
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>