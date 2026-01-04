<div>
    <x-layouts.partners :heading="__('messages.partners.dashboard.overview')">
        <div class="space-y-8">
        
        @php
            $stats = $this->getStats();
            $discountCodes = $this->getDiscountCodes();
            $recentCommissions = $this->getRecentCommissions();
        @endphp
        
        {{-- Stats Grid - 4 Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            
            {{-- Card 1: Current Balance --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute -{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}-4 -top-4 w-24 h-24 bg-primary-50 dark:bg-primary-900/20 rounded-full opacity-50"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                            {{ __('messages.partners.dashboard.balance') }}
                        </p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
                            {{ number_format($stats['balance'], 2) }}
                            <span class="text-base text-gray-500">{{ __('messages.currency.egp') }}</span>
                        </h3>
                    </div>
                    <div class="p-3 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-xl">
                        <i class="ph ph-wallet text-2xl"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="flex items-center text-emerald-600 dark:text-emerald-400 font-semibold">
                        <i class="ph ph-trend-up text-sm m{{ app()->getLocale() === 'ar' ? 'l' : 'r' }}-1"></i>
                        +8.2%
                    </span>
                    <span class="text-gray-400 dark:text-gray-500">{{ __('messages.partners.dashboard.growth_from_last_month') }}</span>
                </div>
                @if($stats['pending_commission'] > 0)
                <div class="mt-4 flex items-center text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 w-fit px-2 py-1 rounded-lg">
                    <i class="ph ph-clock m{{ app()->getLocale() === 'ar' ? 'l' : 'r' }}-1"></i>
                    <span class="font-bold">{{ number_format($stats['pending_commission'], 2) }}</span>
                    <span class="text-gray-400 dark:text-gray-500 m{{ app()->getLocale() === 'ar' ? 'r' : 'l' }}-2 text-xs font-normal">
                        {{ __('messages.partners.dashboard.pending_commission') }}
                    </span>
                </div>
                @endif
            </div>
            
            {{-- Card 2: Total Orders --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                            {{ __('messages.partners.dashboard.total_orders') }}
                        </p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
                            {{ number_format($stats['total_orders']) }}
                        </h3>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-xl">
                        <i class="ph ph-shopping-cart text-2xl"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="flex items-center text-emerald-600 dark:text-emerald-400 font-semibold">
                        <i class="ph ph-trend-up text-sm m{{ app()->getLocale() === 'ar' ? 'l' : 'r' }}-1"></i>
                        +15.3%
                    </span>
                    <span class="text-gray-400 dark:text-gray-500">{{ __('messages.partners.dashboard.growth_from_last_month') }}</span>
                </div>
                {{-- Progress bar --}}
                <div class="mt-3 w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5">
                    <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ min(($stats['total_orders'] / 200) * 100, 100) }}%"></div>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                    {{ __('messages.partners.dashboard.monthly_goal', ['count' => 200]) }}
                </p>
            </div>
            
            {{-- Card 3: Total Earnings --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                            {{ __('messages.partners.dashboard.total_earned') }}
                        </p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
                            {{ number_format($stats['total_earned'], 2) }}
                            <span class="text-base text-gray-500">{{ __('messages.currency.egp') }}</span>
                        </h3>
                    </div>
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <i class="ph ph-coins text-2xl"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="flex items-center text-emerald-600 dark:text-emerald-400 font-semibold">
                        <i class="ph ph-trend-up text-sm m{{ app()->getLocale() === 'ar' ? 'l' : 'r' }}-1"></i>
                        +12.7%
                    </span>
                    <span class="text-gray-400 dark:text-gray-500">{{ __('messages.partners.dashboard.growth_from_last_month') }}</span>
                </div>
                <div class="mt-2 flex items-center text-sm text-emerald-600 dark:text-emerald-400">
                    <span class="font-medium">
                        {{ __('messages.partners.dashboard.total_paid') }}: {{ number_format($stats['total_paid'], 2) }} {{ __('messages.currency.egp') }}
                    </span>
                </div>
            </div>
            
            {{-- Card 4: Total Sales --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                            {{ __('messages.partners.dashboard.total_sales') }}
                        </p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
                            {{ number_format($stats['total_sales'], 2) }}
                            <span class="text-base text-gray-500">{{ __('messages.currency.egp') }}</span>
                        </h3>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl">
                        <i class="ph ph-trend-up text-2xl"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="flex items-center text-emerald-600 dark:text-emerald-400 font-semibold">
                        <i class="ph ph-trend-up text-sm m{{ app()->getLocale() === 'ar' ? 'l' : 'r' }}-1"></i>
                        +18.5%
                    </span>
                    <span class="text-gray-400 dark:text-gray-500">{{ __('messages.partners.dashboard.growth_from_last_month') }}</span>
                </div>
            </div>
        </div>
        
        {{-- Discount Codes Section --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <i class="ph ph-ticket text-primary-600 dark:text-primary-400"></i>
                    {{ __('messages.partners.dashboard.your_codes') }}
                </h2>
            </div>
            
            <div class="p-6">
                <!-- Compact 3-Column Grid for Discount Codes -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($discountCodes as $code)
                        <div class="bg-gradient-to-br from-primary-50 to-purple-50 dark:from-primary-900/20 dark:to-purple-900/20 rounded-xl p-4 border border-primary-100 dark:border-primary-900/30 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400 tracking-wider block">{{ $code->code }}</span>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $code->discount_type === 'percentage' ? $code->discount_value . '%' : number_format($code->discount_value, 2) . ' ' . __('messages.currency.egp') }}
                                    </span>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full">
                                    Active
                                </span>
                            </div>
                            @if($code->used_count > 0)
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                <i class="ph ph-users"></i> {{ $code->used_count }} {{ __('messages.partners.dashboard.uses') ?? 'uses' }}
                            </div>
                            @endif
                            <button
                                x-on:click="navigator.clipboard.writeText('{{ $code->code }}'); 
                                            new FilamentNotification()
                                                .title('{{ __('messages.partners.dashboard.code_copied') }}')
                                                .success()
                                                .send()"
                                class="w-full px-3 py-2 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white text-sm rounded-lg transition-colors flex items-center justify-center gap-2 font-medium">
                                <i class="ph ph-copy"></i>
                                {{ __('messages.partners.dashboard.copy') }}
                            </button>
                        </div>
                        @empty
                    @endforelse
                </div>
                
                @if($discountCodes->isEmpty())
                <div class="text-center py-8">
                    <i class="ph ph-ticket text-6xl text-gray-300 dark:text-gray-700 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ __('messages.partners.dashboard.no_codes') }}
                    </p>
                </div>
                @endif
            </div>
        </div>
        
        {{-- Recent Commissions Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <i class="ph ph-list-dashes text-primary-600 dark:text-primary-400"></i>
                    {{ __('messages.partners.dashboard.recent_commissions') }}
                </h2>
            </div>
            
            @if($recentCommissions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">
                            <tr>
                                <th class="{{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} py-4 px-6">{{ __('messages.partners.dashboard.order_number') }}</th>
                                <th class="{{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} py-4 px-6">{{ __('messages.partners.dashboard.order_total') }}</th>
                                <th class="{{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} py-4 px-6">{{ __('messages.partners.dashboard.your_commission') }}</th>
                                <th class="{{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} py-4 px-6">{{ __('messages.partners.dashboard.status') }}</th>
                                <th class="{{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} py-4 px-6">{{ __('messages.partners.dashboard.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                            @foreach($recentCommissions as $commission)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="py-4 px-6 font-medium text-gray-800 dark:text-gray-200">
                                        {{ $commission->order?->order_number ?? '#' . $commission->order_id }}
                                    </td>
                                    <td class="py-4 px-6 text-gray-600 dark:text-gray-400">
                                        {{ number_format($commission->order_amount, 2) }} {{ __('messages.currency.egp') }}
                                    </td>
                                    <td class="py-4 px-6 font-bold text-emerald-600 dark:text-emerald-400">
                                        +{{ number_format($commission->commission_amount, 2) }} {{ __('messages.currency.egp') }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $commission->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400' : '' }}
                                            {{ $commission->status === 'paid' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : '' }}
                                            {{ $commission->status === 'due' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400' : '' }}
                                            {{ $commission->status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400' : '' }}">
                                            {{ __('messages.partners.dashboard.status_' . $commission->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-500 dark:text-gray-400">
                                        {{ $commission->created_at->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ph ph-chart-line-down text-6xl text-gray-300 dark:text-gray-700 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">
                        {{ __('messages.partners.dashboard.no_commissions') }}
                    </p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
                        {{ __('messages.partners.dashboard.share_code_hint') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.partners>
</div>
