<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex flex-wrap gap-4">
            {{-- Save Button --}}
            <x-filament::button type="submit">
                💾 حفظ الإعدادات
            </x-filament::button>

            {{-- Test Connection Button (for active gateway only) --}}
            @php
                $activeGateway = $this->data['active_gateway'] ?? 'kashier';
            @endphp

            @if($activeGateway === 'paymob')
                <x-filament::button type="button" wire:click="testConnection" color="warning">
                    🔶 اختبار اتصال Paymob
                </x-filament::button>
            @else
                <x-filament::button type="button" wire:click="testConnection" color="info">
                    🔷 اختبار اتصال Kashier
                </x-filament::button>
            @endif
        </div>

        {{-- Active Gateway Info --}}
        <div
            class="mt-4 p-4 rounded-lg {{ $activeGateway === 'paymob' ? 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800' : 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800' }}">
            <div class="flex items-center gap-2">
                <span class="text-lg">
                    @if($activeGateway === 'paymob')
                        🔶
                    @else
                        🔷
                    @endif
                </span>
                <div>
                    <p
                        class="font-semibold {{ $activeGateway === 'paymob' ? 'text-orange-700 dark:text-orange-300' : 'text-blue-700 dark:text-blue-300' }}">
                        البوابة النشطة: {{ $activeGateway === 'paymob' ? 'Paymob (Accept)' : 'Kashier' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        جميع عمليات الدفع ستتم عبر هذه البوابة فقط
                    </p>
                </div>
            </div>
        </div>
    </form>
</x-filament-panels::page>