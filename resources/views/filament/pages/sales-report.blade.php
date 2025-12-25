<x-filament-panels::page>
    {{-- Table with built-in filters --}}
    {{ $this->table }}

    {{-- Print Button --}}
    <div class="mt-6 flex justify-end">
        <x-filament::button color="gray" icon="heroicon-o-printer" onclick="window.print()">
            طباعة التقرير
        </x-filament::button>
    </div>
</x-filament-panels::page>