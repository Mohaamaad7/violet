<div>
    @php
        $stats = $this->getStats();
        $commissions = $this->getCommissions();
        $rtl = app()->getLocale() === 'ar';
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('messages.partners.nav.commissions') }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    تتبع عمولاتك من جميع الطلبات
                </p>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Total Earned --}}
            <div class="bg-gradient-to-br from-violet-500 to-purple-600 p-6 rounded-2xl shadow-lg text-white">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium opacity-90 mb-1">إجمالي العمولات</p>
                <h3 class="text-3xl font-bold">{{ number_format($stats['total_earned'], 2) }}</h3>
                <p class="text-xs opacity-75 mt-1">جنيه مصري</p>
            </div>

            {{-- Pending Balance --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">قيد الانتظار</p>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($stats['pending_balance'], 2) }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">جنيه مصري</p>
            </div>

            {{-- Paid Balance --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">تم الدفع</p>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($stats['paid_balance'], 2) }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">جنيه مصري</p>
            </div>

            {{-- Total Commissions Count --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">عدد العمليات</p>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($stats['total_commissions']) }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">عملية</p>
            </div>

        </div>

        {{-- Filters Section --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
            <div class="flex flex-col lg:flex-row gap-4">
                
                {{-- Status Filter --}}
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        حالة العمولة
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="updateStatusFilter('all')" 
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $statusFilter === 'all' ? 'bg-violet-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            الكل
                        </button>
                        <button wire:click="updateStatusFilter('pending')" 
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $statusFilter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            قيد الانتظار
                        </button>
                        <button wire:click="updateStatusFilter('due')" 
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $statusFilter === 'due' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            مستحقة
                        </button>
                        <button wire:click="updateStatusFilter('paid')" 
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $statusFilter === 'paid' ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            مدفوعة
                        </button>
                        <button wire:click="updateStatusFilter('cancelled')" 
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $statusFilter === 'cancelled' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            ملغية
                        </button>
                    </div>
                </div>

                {{-- Date Filter --}}
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        الفترة الزمنية
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="updateDateFilter('all')" 
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $dateFilter === 'all' ? 'bg-violet-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            الكل
                        </button>
                        <button wire:click="updateDateFilter('today')" 
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $dateFilter === 'today' ? 'bg-violet-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            اليوم
                        </button>
                        <button wire:click="updateDateFilter('week')" 
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $dateFilter === 'week' ? 'bg-violet-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            هذا الأسبوع
                        </button>
                        <button wire:click="updateDateFilter('month')" 
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $dateFilter === 'month' ? 'bg-violet-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            هذا الشهر
                        </button>
                        <button wire:click="updateDateFilter('year')" 
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $dateFilter === 'year' ? 'bg-violet-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            هذا العام
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- Commissions Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    سجل العمولات
                </h2>
            </div>

            @if($commissions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">
                            <tr>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">التاريخ</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">رقم الطلب</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">كود الخصم</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">قيمة الطلب</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">نسبة العمولة</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">مبلغ العمولة</th>
                                <th class="{{ $rtl ? 'text-right' : 'text-left' }} py-4 px-6">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($commissions as $commission)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="py-4 px-6 text-gray-700 dark:text-gray-300">
                                        {{ $commission->created_at->format('Y-m-d') }}
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                                            {{ $commission->created_at->format('h:i A') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-violet-600 dark:text-violet-400 font-medium">
                                            #{{ $commission->order->order_number }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <code class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono">
                                            {{ $commission->discountCode->code }}
                                        </code>
                                    </td>
                                    <td class="py-4 px-6 text-gray-700 dark:text-gray-300">
                                        {{ number_format($commission->order_amount, 2) }} ج.م
                                    </td>
                                    <td class="py-4 px-6 text-gray-700 dark:text-gray-300">
                                        {{ number_format($commission->commission_rate, 1) }}%
                                    </td>
                                    <td class="py-4 px-6 font-bold text-emerald-600 dark:text-emerald-400">
                                        +{{ number_format($commission->commission_amount, 2) }} ج.م
                                    </td>
                                    <td class="py-4 px-6">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400',
                                                'due' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                                                'paid' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                                                'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'قيد الانتظار',
                                                'due' => 'مستحقة',
                                                'paid' => 'مدفوعة',
                                                'cancelled' => 'ملغية',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$commission->status] ?? '' }}">
                                            {{ $statusLabels[$commission->status] ?? $commission->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($commissions->hasPages())
                    <div class="p-6 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            عرض {{ $commissions->firstItem() }} - {{ $commissions->lastItem() }} من {{ $commissions->total() }}
                        </div>
                        <div class="flex gap-2">
                            @if(!$commissions->onFirstPage())
                                <button wire:click="previousPage" 
                                        class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                    السابق
                                </button>
                            @endif
                            @if($commissions->hasMorePages())
                                <button wire:click="nextPage" 
                                        class="px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors">
                                    التالي
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-16">
                    <svg class="w-24 h-24 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">لا توجد عمولات بعد</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">ستظهر عمولاتك هنا عند إتمام أول طلب</p>
                </div>
            @endif
        </div>

    </div>
</div>
