<div>
    @php
        $stats = $this->getStats();
        $codes = $this->getDiscountCodes();
        $rtl = app()->getLocale() === 'ar';
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('messages.partners.nav.discount_codes') }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    إدارة أكواد الخصم الخاصة بك ومتابعة أدائها
                </p>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Total Codes --}}
            <div class="bg-gradient-to-br from-blue-500 to-cyan-600 p-6 rounded-2xl shadow-lg text-white">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium opacity-90 mb-1">إجمالي الأكواد</p>
                <h3 class="text-3xl font-bold">{{ number_format($stats['total_codes']) }}</h3>
                <p class="text-xs opacity-75 mt-1">كود</p>
            </div>

            {{-- Active Codes --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">الأكواد النشطة</p>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($stats['active_codes']) }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">كود فعّال</p>
            </div>

            {{-- Total Uses --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">مرات الاستخدام</p>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($stats['total_uses']) }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">استخدام</p>
            </div>

            {{-- Total Discount Given --}}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">خصم ممنوح</p>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($stats['total_discount_given'], 2) }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">جنيه مصري</p>
            </div>

        </div>

        {{-- Codes Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($codes as $code)
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden hover:shadow-lg transition-shadow">
                    {{-- Header with Status Badge --}}
                    <div class="p-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <code class="text-2xl font-bold text-violet-600 dark:text-violet-400 tracking-wider">
                                        {{ $code->code }}
                                    </code>
                                    <button onclick="navigator.clipboard.writeText('{{ $code->code }}')" 
                                            class="p-1.5 text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($code->is_active)
                                        <span class="px-2.5 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-medium rounded-full">
                                            نشط
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-medium rounded-full">
                                            غير نشط
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Discount Value --}}
                        <div class="bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20 rounded-xl p-4">
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">قيمة الخصم</p>
                            <p class="text-2xl font-bold text-violet-600 dark:text-violet-400">
                                @if($code->discount_type === 'percentage')
                                    {{ number_format($code->discount_value, 0) }}%
                                @else
                                    {{ number_format($code->discount_value, 2) }} ج.م
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Statistics --}}
                    <div class="p-6 pt-4 space-y-3">
                        {{-- Usage Count --}}
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                مرات الاستخدام
                            </span>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                {{ number_format($code->times_used) }}
                                @if($code->usage_limit)
                                    <span class="text-gray-400">/ {{ number_format($code->usage_limit) }}</span>
                                @endif
                            </span>
                        </div>

                        {{-- Orders Count --}}
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                عدد الطلبات
                            </span>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                {{ number_format($code->orders_count ?? 0) }}
                            </span>
                        </div>

                        {{-- Total Discount Given --}}
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                إجمالي الخصم
                            </span>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                {{ number_format($code->usages_sum_discount_amount ?? 0, 2) }} ج.م
                            </span>
                        </div>

                        {{-- Commission Rate --}}
                        <div class="flex justify-between items-center pt-3 border-t border-gray-100 dark:border-gray-800">
                            <span class="text-sm text-gray-600 dark:text-gray-400">نسبة العمولة</span>
                            <span class="text-sm font-bold text-violet-600 dark:text-violet-400">
                                @if($code->commission_type === 'percentage')
                                    {{ number_format($code->commission_value, 1) }}%
                                @else
                                    {{ number_format($code->commission_value, 2) }} ج.م
                                @endif
                            </span>
                        </div>

                        {{-- Validity Period --}}
                        @if($code->expires_at)
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-500 dark:text-gray-400">ينتهي في</span>
                                <span class="{{ $code->expires_at->isPast() ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300' }} font-medium">
                                    {{ $code->expires_at->format('Y-m-d') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16">
                    <svg class="w-24 h-24 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">لا توجد أكواد خصم بعد</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">سيتم إنشاء كود خصم خاص بك عند الموافقة على حسابك</p>
                </div>
            @endforelse
        </div>

    </div>
</div>
