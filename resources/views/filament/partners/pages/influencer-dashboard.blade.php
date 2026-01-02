<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Greeting --}}
        <div class="text-center py-4">
            <h2 class="text-xl font-bold">
                {{ __('messages.partners.dashboard.greeting', ['name' => $this->getUserName()]) }}
            </h2>
            <p class="text-gray-500 mt-1">
                {{ __('messages.partners.dashboard.welcome_message') }}
            </p>
        </div>

        @php
            $stats = $this->getStats();
            $discountCodes = $this->getDiscountCodes();
            $recentCommissions = $this->getRecentCommissions();
        @endphp

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-success-600">
                        {{ number_format($stats['balance'], 2) }}
                    </div>
                    <div class="text-sm text-gray-500">{{ __('messages.partners.dashboard.balance') }}</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-600">
                        {{ number_format($stats['total_earned'], 2) }}
                    </div>
                    <div class="text-sm text-gray-500">{{ __('messages.partners.dashboard.total_earned') }}</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-warning-600">
                        {{ number_format($stats['total_orders']) }}
                    </div>
                    <div class="text-sm text-gray-500">{{ __('messages.partners.dashboard.total_orders') }}</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-info-600">
                        {{ number_format($stats['total_sales'], 2) }}
                    </div>
                    <div class="text-sm text-gray-500">{{ __('messages.partners.dashboard.total_sales') }}</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Discount Codes --}}
        <x-filament::section>
            <x-slot name="heading">
                {{ __('messages.partners.dashboard.your_codes') }}
            </x-slot>

            @forelse($discountCodes as $code)
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg mb-3">
                    <div>
                        <span class="text-xl font-bold text-primary-600">{{ $code->code }}</span>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $code->discount_type === 'percentage' ? $code->discount_value . '%' : number_format($code->discount_value, 2) . ' ' . __('messages.currency.egp') }}
                            {{ __('messages.partners.dashboard.discount_for_followers') }}
                        </p>
                    </div>
                    <x-filament::button color="primary" size="sm"
                        x-on:click="navigator.clipboard.writeText('{{ $code->code }}'); $notification.success({ title: '{{ __('messages.partners.dashboard.code_copied') }}' })">
                        {{ __('messages.partners.dashboard.copy') }}
                    </x-filament::button>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">
                    {{ __('messages.partners.dashboard.no_codes') }}
                </p>
            @endforelse
        </x-filament::section>

        {{-- Recent Commissions --}}
        <x-filament::section>
            <x-slot name="heading">
                {{ __('messages.partners.dashboard.recent_commissions') }}
            </x-slot>

            @if($recentCommissions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-right py-2 px-3">{{ __('messages.partners.dashboard.order_number') }}</th>
                                <th class="text-right py-2 px-3">{{ __('messages.partners.dashboard.order_total') }}</th>
                                <th class="text-right py-2 px-3">{{ __('messages.partners.dashboard.your_commission') }}
                                </th>
                                <th class="text-right py-2 px-3">{{ __('messages.partners.dashboard.status') }}</th>
                                <th class="text-right py-2 px-3">{{ __('messages.partners.dashboard.date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCommissions as $commission)
                                <tr class="border-b">
                                    <td class="py-2 px-3">{{ $commission->order?->order_number ?? '#' . $commission->order_id }}
                                    </td>
                                    <td class="py-2 px-3">{{ number_format($commission->order_total, 2) }}
                                        {{ __('messages.currency.egp') }}</td>
                                    <td class="py-2 px-3 text-success-600 font-semibold">
                                        +{{ number_format($commission->commission_amount, 2) }}</td>
                                    <td class="py-2 px-3">
                                        <x-filament::badge :color="match ($commission->status) { 'pending' => 'warning', 'paid' => 'success', default => 'gray'}">
                                            {{ __('messages.partners.dashboard.status_' . $commission->status) }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-2 px-3 text-gray-500">{{ $commission->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>{{ __('messages.partners.dashboard.no_commissions') }}</p>
                    <p class="text-sm mt-2">{{ __('messages.partners.dashboard.share_code_hint') }}</p>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>