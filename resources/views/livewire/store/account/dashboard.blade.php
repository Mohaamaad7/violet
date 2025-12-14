<div class="bg-cream-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.account.dashboard') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('messages.account.welcome_back', ['name' => $user->name]) }}</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Total Orders --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-violet-100 text-violet-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('messages.account.total_orders') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Pending Orders --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('messages.account.pending_orders') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_orders'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Delivered Orders --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('messages.account.delivered_orders') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['delivered_orders'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Total Spent --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-emerald-100 text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('messages.account.total_spent') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_spent'], 2) }}
                            {{ __('messages.egp') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Links & Recent Orders --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Quick Links --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.account.quick_links') }}</h2>
                    </div>
                    <nav class="divide-y divide-gray-100">
                        <a href="{{ route('account.profile') }}"
                            class="flex items-center px-6 py-4 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="ms-3 text-gray-700">{{ __('messages.account.profile') }}</span>
                            <svg class="w-5 h-5 ms-auto text-gray-400 rtl:rotate-180" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>
                        <a href="{{ route('account.orders') }}"
                            class="flex items-center px-6 py-4 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span class="ms-3 text-gray-700">{{ __('messages.account.orders') }}</span>
                            @if($stats['pending_orders'] > 0)
                                <span
                                    class="ms-auto me-2 px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    {{ $stats['pending_orders'] }}
                                </span>
                            @endif
                            <svg class="w-5 h-5 text-gray-400 rtl:rotate-180" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>
                        <a href="{{ route('account.addresses') }}"
                            class="flex items-center px-6 py-4 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="ms-3 text-gray-700">{{ __('messages.account.addresses') }}</span>
                            <span
                                class="ms-auto me-2 px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">
                                {{ $addressesCount }}
                            </span>
                            <svg class="w-5 h-5 text-gray-400 rtl:rotate-180" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Recent Orders --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.account.recent_orders') }}</h2>
                        <a href="{{ route('account.orders') }}"
                            class="text-sm text-violet-600 hover:text-violet-800 font-medium">
                            {{ __('messages.view_all') }}
                        </a>
                    </div>

                    @if($recentOrders->isEmpty())
                        <div class="p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.account.no_orders') }}</h3>
                            <p class="mt-2 text-gray-500">{{ __('messages.account.no_orders_desc') }}</p>
                            <a href="{{ route('products.index') }}"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors">
                                {{ __('messages.shop_now') }}
                            </a>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($recentOrders as $order)
                                <a href="{{ route('account.orders.show', $order) }}"
                                    class="block p-6 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <span class="font-semibold text-gray-900">#{{ $order->order_number }}</span>
                                            <span
                                                class="text-sm text-gray-500 ms-2">{{ $order->created_at->format('M d, Y') }}</span>
                                        </div>
                                        @php
                                            $statusColorMap = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'processing' => 'bg-blue-100 text-blue-800',
                                                'shipped' => 'bg-purple-100 text-purple-800',
                                                'delivered' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                            ];
                                            $statusKey = $order->status?->toString() ?? 'pending';
                                            $statusLabel = $order->status?->label() ?? __('messages.account.status.pending');
                                        @endphp
                                        <span
                                            class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColorMap[$statusKey] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500">
                                            {{ $order->items->count() }} {{ __('messages.account.items') }}
                                        </span>
                                        <span class="font-semibold text-gray-900">
                                            {{ number_format($order->total, 2) }} {{ __('messages.egp') }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>