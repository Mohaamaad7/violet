<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Greeting --}}
        <div class="fi-header">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                👋 {{ __('messages.partners.dashboard.greeting', ['name' => $this->getUserName()]) }}
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                {{ __('messages.partners.dashboard.welcome_message') }}
            </p>
        </div>

        @php
            $stats = $this->getStats();
            $discountCodes = $this->getDiscountCodes();
            $recentCommissions = $this->getRecentCommissions();
        @endphp

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Balance Card --}}
            <div
                class="fi-wi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <div
                        class="fi-wi-stats-overview-stat-icon-ctn flex h-12 w-12 items-center justify-center rounded-full bg-success-50 dark:bg-success-900/20">
                        <x-heroicon-o-banknotes class="h-6 w-6 text-success-600 dark:text-success-400" />
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('messages.partners.dashboard.balance') }}
                        </span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100 block">
                            {{ number_format($stats['balance'], 2) }} {{ __('messages.currency.egp') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Total Earned --}}
            <div
                class="fi-wi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <div
                        class="fi-wi-stats-overview-stat-icon-ctn flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-900/20">
                        <x-heroicon-o-chart-bar class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('messages.partners.dashboard.total_earned') }}
                        </span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100 block">
                            {{ number_format($stats['total_earned'], 2) }} {{ __('messages.currency.egp') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Total Orders --}}
            <div
                class="fi-wi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <div
                        class="fi-wi-stats-overview-stat-icon-ctn flex h-12 w-12 items-center justify-center rounded-full bg-warning-50 dark:bg-warning-900/20">
                        <x-heroicon-o-shopping-bag class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('messages.partners.dashboard.total_orders') }}
                        </span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100 block">
                            {{ number_format($stats['total_orders']) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Total Sales --}}
            <div
                class="fi-wi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <div
                        class="fi-wi-stats-overview-stat-icon-ctn flex h-12 w-12 items-center justify-center rounded-full bg-info-50 dark:bg-info-900/20">
                        <x-heroicon-o-currency-dollar class="h-6 w-6 text-info-600 dark:text-info-400" />
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('messages.partners.dashboard.total_sales') }}
                        </span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100 block">
                            {{ number_format($stats['total_sales'], 2) }} {{ __('messages.currency.egp') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Discount Codes Section --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <x-heroicon-o-ticket class="h-5 w-5 text-primary-500" />
                {{ __('messages.partners.dashboard.your_codes') }}
            </h2>

            @forelse($discountCodes as $code)
                <div
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-primary-50 to-purple-50 dark:from-primary-900/20 dark:to-purple-900/20 rounded-lg mb-3">
                    <div>
                        <span class="text-2xl font-bold text-primary-700 dark:text-primary-300">
                            {{ $code->code }}
                        </span>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $code->discount_type === 'percentage' ? $code->discount_value . '%' : number_format($code->discount_value, 2) . ' ' . __('messages.currency.egp') }}
                            {{ __('messages.partners.dashboard.discount_for_followers') }}
                        </p>
                    </div>
                    <button
                        onclick="navigator.clipboard.writeText('{{ $code->code }}'); alert('{{ __('messages.partners.dashboard.code_copied') }}')"
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center gap-2 transition-colors">
                        <x-heroicon-s-clipboard-document class="h-5 w-5" />
                        {{ __('messages.partners.dashboard.copy') }}
                    </button>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400">
                    {{ __('messages.partners.dashboard.no_codes') }}
                </p>
            @endforelse
        </div>

        {{-- Recent Commissions Table --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <x-heroicon-o-clock class="h-5 w-5 text-success-500" />
                {{ __('messages.partners.dashboard.recent_commissions') }}
            </h2>

            @if($recentCommissions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-right py-3 px-4 font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('messages.partners.dashboard.order_number') }}</th>
                                <th class="text-right py-3 px-4 font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('messages.partners.dashboard.order_total') }}</th>
                                <th class="text-right py-3 px-4 font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('messages.partners.dashboard.your_commission') }}</th>
                                <th class="text-right py-3 px-4 font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('messages.partners.dashboard.status') }}</th>
                                <th class="text-right py-3 px-4 font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('messages.partners.dashboard.date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCommissions as $commission)
                                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                                        <td class="py-3 px-4 text-gray-900 dark:text-gray-100">
                                                            {{ $commission->order?->order_number ?? '#' . $commission->order_id }}
                                                        </td>
                                                        <td class="py-3 px-4 text-gray-900 dark:text-gray-100">
                                                            {{ number_format($commission->order_total, 2) }} {{ __('messages.currency.egp') }}
                                                        </td>
                                                        <td class="py-3 px-4 text-success-600 dark:text-success-400 font-semibold">
                                                            +{{ number_format($commission->commission_amount, 2) }}
                                                            {{ __('messages.currency.egp') }}
                                                        </td>
                                                        <td class="py-3 px-4">
                                                            @php
                                                                $statusColors = [
                                                                    'pending' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400',
                                                                    'paid' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400',
                                                                    'cancelled' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400',
                                                                ];
                                                            @endphp
                                 <span
                                                                class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$commission->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                                {{ __('messages.partners.dashboard.status_' . $commission->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="py-3 px-4 text-gray-500 dark:text-gray-400">
                                                            {{ $commission->created_at->format('Y-m-d') }}
                                                        </td>
                                                    </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-inbox class="h-12 w-12 mx-auto mb-3 opacity-50" />
                    <p>{{ __('messages.partners.dashboard.no_commissions') }}</p>
                    <p class="text-sm mt-1">{{ __('messages.partners.dashboard.share_code_hint') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>