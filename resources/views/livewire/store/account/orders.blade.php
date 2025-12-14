<div class="bg-cream-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Back Link --}}
        <a href="{{ route('account.dashboard') }}"
            class="inline-flex items-center text-sm text-gray-600 hover:text-violet-600 mb-6">
            <svg class="w-4 h-4 me-1 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            {{ __('messages.account.back_to_dashboard') }}
        </a>

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.account.orders') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('messages.account.orders_subtitle') }}</p>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                {{-- Search --}}
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('messages.account.search_orders') }}"
                            class="w-full ps-10 pe-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors">
                        <svg class="w-5 h-5 text-gray-400 absolute start-3 top-1/2 -translate-y-1/2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="w-full md:w-48">
                    <select wire:model.live="status"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors">
                        <option value="">{{ __('messages.account.all_statuses') }} ({{ $statusCounts['all'] }})</option>
                        <option value="pending">{{ __('messages.account.status.pending') }}
                            ({{ $statusCounts['pending'] }})</option>
                        <option value="processing">{{ __('messages.account.status.processing') }}
                            ({{ $statusCounts['processing'] }})</option>
                        <option value="shipped">{{ __('messages.account.status.shipped') }}
                            ({{ $statusCounts['shipped'] }})</option>
                        <option value="delivered">{{ __('messages.account.status.delivered') }}
                            ({{ $statusCounts['delivered'] }})</option>
                        <option value="cancelled">{{ __('messages.account.status.cancelled') }}
                            ({{ $statusCounts['cancelled'] }})</option>
                    </select>
                </div>

                @if($search || $status)
                    <button wire:click="clearFilters"
                        class="px-4 py-3 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('messages.account.clear_filters') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Orders List --}}
        @if($orders->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.account.no_orders') }}</h3>
                <p class="mt-2 text-gray-500">
                    @if($search || $status)
                        {{ __('messages.account.no_orders_filter') }}
                    @else
                        {{ __('messages.account.no_orders_desc') }}
                    @endif
                </p>
                @if(!$search && !$status)
                    <a href="{{ route('products.index') }}"
                        class="mt-4 inline-flex items-center px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors">
                        {{ __('messages.shop_now') }}
                    </a>
                @endif
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                        {{-- Order Header --}}
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">{{ __('messages.account.order_number') }}</p>
                                        <p class="font-semibold text-gray-900">#{{ $order->order_number }}</p>
                                    </div>
                                    <div class="h-8 w-px bg-gray-200 hidden md:block"></div>
                                    <div>
                                        <p class="text-sm text-gray-500">{{ __('messages.account.order_date') }}</p>
                                        <p class="font-semibold text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
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
                                        class="px-3 py-1 text-sm font-medium rounded-full {{ $statusColorMap[$statusKey] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $statusLabel }}
                                    </span>
                                    <a href="{{ route('account.orders.show', $order) }}"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-violet-600 border border-violet-600 rounded-lg hover:bg-violet-50 transition-colors">
                                        {{ __('messages.account.view_details') }}
                                        <svg class="w-4 h-4 ms-2 rtl:rotate-180" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Order Items Preview --}}
                        <div class="p-6">
                            <div class="flex items-center gap-4 overflow-x-auto pb-2">
                                @foreach($order->items->take(4) as $item)
                                    <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg overflow-hidden">
                                        @if($item->product && $item->product->getFirstMediaUrl('images', 'thumb'))
                                            <img src="{{ $item->product->getFirstMediaUrl('images', 'thumb') }}"
                                                alt="{{ $item->product->name ?? '' }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                @if($order->items->count() > 4)
                                    <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-500">+{{ $order->items->count() - 4 }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">
                                    {{ $order->items->count() }} {{ __('messages.account.items') }}
                                </span>
                                <span class="text-lg font-bold text-gray-900">
                                    {{ number_format($order->total, 2) }} {{ __('messages.egp') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>