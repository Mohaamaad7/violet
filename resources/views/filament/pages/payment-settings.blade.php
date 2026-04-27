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
                $gatewayConfig = match($activeGateway) {
                    'paymob' => ['name' => 'Paymob', 'icon' => '🔶', 'color' => 'warning', 'bg' => 'orange'],
                    'fawry'  => ['name' => 'Fawry Pay', 'icon' => '🟡', 'color' => 'success', 'bg' => 'yellow'],
                    default  => ['name' => 'Kashier', 'icon' => '🔷', 'color' => 'info', 'bg' => 'blue'],
                };
            @endphp

            <x-filament::button type="button" wire:click="testConnection" color="{{ $gatewayConfig['color'] }}">
                {{ $gatewayConfig['icon'] }} اختبار اتصال {{ $gatewayConfig['name'] }}
            </x-filament::button>
        </div>

        {{-- Active Gateway Info --}}
        @php
            $bgClasses = match($activeGateway) {
                'paymob' => 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800',
                'fawry'  => 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800',
                default  => 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800',
            };
            $textClasses = match($activeGateway) {
                'paymob' => 'text-orange-700 dark:text-orange-300',
                'fawry'  => 'text-yellow-700 dark:text-yellow-300',
                default  => 'text-blue-700 dark:text-blue-300',
            };
        @endphp
        <div class="mt-4 p-4 rounded-lg {{ $bgClasses }}">
            <div class="flex items-center gap-2">
                <span class="text-lg">{{ $gatewayConfig['icon'] }}</span>
                <div>
                    <p class="font-semibold {{ $textClasses }}">
                        البوابة النشطة: {{ $gatewayConfig['name'] }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        جميع عمليات الدفع ستتم عبر هذه البوابة فقط
                    </p>
                </div>
            </div>
        </div>
    </form>
</x-filament-panels::page>