<div>
    @php
        $stats = $this->getStats();
        $payouts = $this->getPayouts();
        $rtl = app()->getLocale() === 'ar';
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('messages.partners.nav.payouts') }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    إدارة طلبات سحب الأرباح ومتابعة حالتها
                </p>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Available Balance --}}
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 p-6 rounded-2xl shadow-lg text-white">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium opacity-90 mb-1">الرصيد المتاح</p>
                <h3 class="text-3xl font-bold">{{ number_format($stats['available_balance'], 2) }}</h3>
                <p class="text-xs opacity-75 mt-1">جنيه مصري</p>
            </div>

            {{-- Total Withdrawn --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">إجمالي المسحوبات</p>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($stats['total_withdrawn'], 2) }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">جنيه مصري</p>
            </div>

            {{-- Pending Requests --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">طلبات معلقة</p>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($stats['pending_requests']) }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">طلب</p>
            </div>

            {{-- Total Payouts --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">إجمالي الطلبات</p>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($stats['total_payouts']) }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">طلب</p>
            </div>

        </div>

        {{-- Info Alert --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">كيفية طلب سحب الأرباح</h4>
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        للحصول على أرباحك، يرجى التواصل مع الإدارة عبر البريد الإلكتروني أو الهاتف. سيتم مراجعة طلبك ومعالجته خلال 3-5 أيام عمل.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-3">
                        <a href="mailto:payouts@flowerviolet.com" 
                           class="inline-flex items-center gap-2 text-sm font-medium text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            payouts@flowerviolet.com
                        </a>
                        <a href="tel:+201234567890" 
                           class="inline-flex items-center gap-2 text-sm font-medium text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            +20 123 456 7890
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payouts History Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    سجل طلبات الصرف
                </h2>
            </div>

            @if($payouts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">
                            <tr>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">رقم الطلب</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">التاريخ</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">المبلغ</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">طريقة الدفع</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">الحالة</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">رقم المعاملة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($payouts as $payout)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="py-4 px-6 font-medium text-violet-600 dark:text-violet-400">
                                        #{{ str_pad($payout->id, 6, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-4 px-6 text-gray-700 dark:text-gray-300">
                                        {{ $payout->created_at->format('Y-m-d') }}
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                                            {{ $payout->created_at->format('h:i A') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-bold text-gray-800 dark:text-gray-200">
                                        {{ number_format($payout->amount, 2) }} ج.م
                                    </td>
                                    <td class="py-4 px-6 text-gray-700 dark:text-gray-300">
                                        @php
                                            $methodLabels = [
                                                'bank_transfer' => 'تحويل بنكي',
                                                'cash' => 'نقداً',
                                                'wallet' => 'محفظة إلكترونية',
                                            ];
                                        @endphp
                                        {{ $methodLabels[$payout->method] ?? $payout->method }}
                                    </td>
                                    <td class="py-4 px-6">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400',
                                                'approved' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                                                'paid' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                                                'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'معلق',
                                                'approved' => 'موافق عليه',
                                                'paid' => 'تم الدفع',
                                                'rejected' => 'مرفوض',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$payout->status] ?? '' }}">
                                            {{ $statusLabels[$payout->status] ?? $payout->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($payout->transaction_reference)
                                            <code class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono">
                                                {{ $payout->transaction_reference }}
                                            </code>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-16">
                    <svg class="w-24 h-24 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">لا توجد طلبات صرف بعد</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">سيظهر سجل طلبات الصرف هنا عند تقديم أول طلب</p>
                </div>
            @endif
        </div>

    </div>
</div>
