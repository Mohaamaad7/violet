<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex gap-4">
            <x-filament::button type="submit">
                حفظ الإعدادات
            </x-filament::button>

            <x-filament::button type="button" wire:click="testConnection" color="gray">
                اختبار الاتصال
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>