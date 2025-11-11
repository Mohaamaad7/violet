<div>
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Products Card -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">المنتجات</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($stats['products']['total']) }}</h3>
                    <p class="text-purple-200 text-xs mt-1">نشط: {{ $stats['products']['active'] }}</p>
                </div>
                <div class="bg-white/20 p-4 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Categories Card -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">الفئات</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($stats['categories']['total']) }}</h3>
                    <p class="text-blue-200 text-xs mt-1">نشط: {{ $stats['categories']['active'] }}</p>
                </div>
                <div class="bg-white/20 p-4 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">الطلبات</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($stats['orders']['total']) }}</h3>
                    <p class="text-green-200 text-xs mt-1">قيد الانتظار: {{ $stats['orders']['pending'] }}</p>
                </div>
                <div class="bg-white/20 p-4 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-700 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-100 text-sm">إجمالي المبيعات</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($stats['orders']['total_revenue']) }}</h3>
                    <p class="text-amber-200 text-xs mt-1">جنيه مصري</p>
                </div>
                <div class="bg-white/20 p-4 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Stock Alerts -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 ml-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                تنبيهات المخزون
            </h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <span class="text-sm text-gray-700">منتجات منخفضة المخزون</span>
                    <span class="px-3 py-1 bg-yellow-500 text-white text-sm font-bold rounded-full">{{ $stats['products']['low_stock'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                    <span class="text-sm text-gray-700">منتجات متوفرة</span>
                    <span class="px-3 py-1 bg-green-500 text-white text-sm font-bold rounded-full">{{ $stats['products']['in_stock'] }}</span>
                </div>
            </div>
        </div>

        <!-- Users Stats -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 ml-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                المستخدمين
            </h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <span class="text-sm text-gray-700">إجمالي المستخدمين</span>
                    <span class="px-3 py-1 bg-blue-500 text-white text-sm font-bold rounded-full">{{ $stats['users']['total'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-purple-50 border border-purple-200 rounded-lg">
                    <span class="text-sm text-gray-700">العملاء</span>
                    <span class="px-3 py-1 bg-purple-500 text-white text-sm font-bold rounded-full">{{ $stats['users']['customers'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Message -->
    <div class="mt-6 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-lg p-8 text-white">
        <h2 class="text-2xl font-bold mb-2">مرحباً بك في لوحة تحكم Violet! 🌸</h2>
        <p class="text-purple-100">إليك نظرة سريعة على أداء متجرك</p>
    </div>
</div>
