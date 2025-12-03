<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.track_order.title') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('messages.track_order.subtitle') }}</p>
        </div>

        {{-- Search Form --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
            <form wire:submit="track" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Order Number --}}
                    <div>
                        <label for="orderNumber" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.track_order.order_number') }}
                        </label>
                        <input
                            wire:model="orderNumber"
                            type="text"
                            id="orderNumber"
                            placeholder="{{ __('messages.track_order.order_number_placeholder') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500"
                        />
                        @error('orderNumber')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email or Phone --}}
                    <div>
                        <label for="contactInfo" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.track_order.contact_info') }}
                        </label>
                        <input
                            wire:model="contactInfo"
                            type="text"
                            id="contactInfo"
                            placeholder="{{ __('messages.track_order.contact_info_placeholder') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500"
                        />
                        @error('contactInfo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-center gap-4">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-6 py-3 bg-violet-600 text-white font-semibold rounded-lg hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="track">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                        </span>
                        <span wire:loading wire:target="track">
                            <svg class="animate-spin w-5 h-5 ltr:mr-2 rtl:ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        {{ __('messages.track_order.search') }}
                    </button>

                    @if($searched)
                        <button
                            type="button"
                            wire:click="clear"
                            class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition"
                        >
                            <x-heroicon-o-arrow-path class="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                            {{ __('messages.track_order.new_search') }}
                        </button>
                    @endif
                </div>
            </form>
        </div>

        {{-- Error Message --}}
        @if($errorMessage)
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
                <div class="flex items-center">
                    <x-heroicon-o-exclamation-circle class="w-8 h-8 text-red-500 ltr:mr-4 rtl:ml-4" />
                    <div>
                        <h3 class="text-lg font-semibold text-red-800">{{ __('messages.track_order.error_title') }}</h3>
                        <p class="text-red-600">{{ $errorMessage }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Order Found --}}
        @if($order)
            <div class="space-y-8">
                {{-- Order Summary Card --}}
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="bg-violet-700 px-6 py-4 border-b-4 border-amber-400">
                        <div class="flex items-center justify-between text-white">
                            <div>
                                <p class="text-sm opacity-80">{{ __('messages.track_order.order_number') }}</p>
                                <p class="text-2xl font-bold">{{ $order->order_number }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm opacity-80">{{ __('messages.track_order.order_date') }}</p>
                                <p class="text-lg font-semibold">{{ $order->created_at->format('Y-m-d') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-sm text-gray-500">{{ __('messages.track_order.status_label') }}</p>
                                <span class="inline-flex items-center mt-1 px-3 py-1 rounded-full text-sm font-medium
                                    @switch($order->status)
                                        @case('pending') bg-gray-100 text-gray-800 @break
                                        @case('confirmed') bg-blue-100 text-blue-800 @break
                                        @case('processing') bg-yellow-100 text-yellow-800 @break
                                        @case('shipped') bg-purple-100 text-purple-800 @break
                                        @case('delivered') bg-green-100 text-green-800 @break
                                        @case('cancelled') bg-red-100 text-red-800 @break
                                    @endswitch
                                ">
                                    {{ __('messages.track_order.status.' . $order->status) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('messages.track_order.payment_status') }}</p>
                                <span class="inline-flex items-center mt-1 px-3 py-1 rounded-full text-sm font-medium
                                    {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}
                                ">
                                    {{ __('messages.track_order.payment.' . $order->payment_status) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('messages.track_order.items_count') }}</p>
                                <p class="text-lg font-semibold text-gray-900 mt-1">{{ $order->items->count() }} {{ __('messages.track_order.items') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('messages.track_order.total_amount') }}</p>
                                <p class="text-lg font-semibold text-violet-600 mt-1">{{ number_format($order->total, 2) }} {{ __('messages.currency') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Timeline --}}
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">{{ __('messages.track_order.timeline_title') }}</h3>
                    
                    <div class="relative">
                        {{-- Timeline Line --}}
                        <div class="absolute ltr:left-6 rtl:right-6 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        
                        <div class="space-y-8">
                            @foreach($this->statusTimeline as $step)
                                <div class="relative flex items-start">
                                    {{-- Icon Circle --}}
                                    <div class="relative z-10 flex items-center justify-center w-12 h-12 rounded-full
                                        @if($step['current'] ?? false)
                                            bg-{{ $step['color'] }}-500 text-white ring-4 ring-{{ $step['color'] }}-200
                                        @elseif($step['completed'])
                                            bg-{{ $step['color'] }}-500 text-white
                                        @else
                                            bg-gray-200 text-gray-400
                                        @endif
                                    ">
                                        @switch($step['icon'])
                                            @case('clock')
                                                <x-heroicon-o-clock class="w-6 h-6" />
                                                @break
                                            @case('check-circle')
                                                <x-heroicon-o-check-circle class="w-6 h-6" />
                                                @break
                                            @case('cog')
                                                <x-heroicon-o-cog-6-tooth class="w-6 h-6" />
                                                @break
                                            @case('truck')
                                                <x-heroicon-o-truck class="w-6 h-6" />
                                                @break
                                            @case('check-badge')
                                                <x-heroicon-o-check-badge class="w-6 h-6" />
                                                @break
                                            @case('x-circle')
                                                <x-heroicon-o-x-circle class="w-6 h-6" />
                                                @break
                                        @endswitch
                                    </div>
                                    
                                    {{-- Content --}}
                                    <div class="ltr:ml-4 rtl:mr-4 flex-1">
                                        <h4 class="text-base font-semibold {{ $step['completed'] ? 'text-gray-900' : 'text-gray-400' }}">
                                            {{ $step['label'] }}
                                            @if($step['current'] ?? false)
                                                <span class="inline-flex items-center ltr:ml-2 rtl:mr-2 px-2 py-0.5 rounded text-xs font-medium bg-violet-100 text-violet-800">
                                                    {{ __('messages.track_order.current') }}
                                                </span>
                                            @endif
                                        </h4>
                                        @if($step['date'])
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ $step['date']->format('Y-m-d H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Order Items --}}
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.track_order.order_items') }}</h3>
                    </div>
                    
                    <div class="divide-y">
                        @foreach($order->items as $item)
                            <div class="p-6 flex items-center gap-4">
                                {{-- Product Image --}}
                                @if($item->product?->getFirstMediaUrl('images', 'thumb'))
                                    <img 
                                        src="{{ $item->product->getFirstMediaUrl('images', 'thumb') }}" 
                                        alt="{{ $item->product_name }}"
                                        class="w-16 h-16 object-cover rounded-lg"
                                    />
                                @else
                                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <x-heroicon-o-photo class="w-8 h-8 text-gray-400" />
                                    </div>
                                @endif
                                
                                {{-- Product Details --}}
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-base font-medium text-gray-900 truncate">{{ $item->product_name }}</h4>
                                    @if($item->variant_name)
                                        <p class="text-sm text-gray-500">{{ $item->variant_name }}</p>
                                    @endif
                                    <p class="text-sm text-gray-500">
                                        {{ __('messages.track_order.quantity') }}: {{ $item->quantity }}
                                    </p>
                                </div>
                                
                                {{-- Price --}}
                                <div class="text-right">
                                    <p class="text-base font-semibold text-gray-900">
                                        {{ number_format($item->subtotal, 2) }} {{ __('messages.currency') }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ number_format($item->unit_price, 2) }} × {{ $item->quantity }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Order Totals --}}
                    <div class="bg-gray-50 px-6 py-4">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">{{ __('messages.track_order.subtotal') }}</span>
                                <span class="text-gray-900">{{ number_format($order->subtotal, 2) }} {{ __('messages.currency') }}</span>
                            </div>
                            @if($order->discount_amount > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>{{ __('messages.track_order.discount') }}</span>
                                    <span>-{{ number_format($order->discount_amount, 2) }} {{ __('messages.currency') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-500">{{ __('messages.track_order.shipping') }}</span>
                                <span class="text-gray-900">{{ number_format($order->shipping_cost, 2) }} {{ __('messages.currency') }}</span>
                            </div>
                            @if($order->tax_amount > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">{{ __('messages.track_order.tax') }}</span>
                                    <span class="text-gray-900">{{ number_format($order->tax_amount, 2) }} {{ __('messages.currency') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between pt-2 border-t border-gray-200 text-base font-semibold">
                                <span class="text-gray-900">{{ __('messages.track_order.total') }}</span>
                                <span class="text-violet-600">{{ number_format($order->total, 2) }} {{ __('messages.currency') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shipping Address --}}
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.track_order.shipping_address') }}</h3>
                    
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-map-pin class="w-5 h-5 text-gray-400 mt-0.5" />
                        <div>
                            @if($order->guest_name)
                                <p class="font-medium text-gray-900">{{ $order->guest_name }}</p>
                                <p class="text-gray-600">{{ $order->guest_phone }}</p>
                                <p class="text-gray-600">{{ $order->guest_address }}</p>
                                <p class="text-gray-600">{{ $order->guest_city }}, {{ $order->guest_governorate }}</p>
                            @elseif($order->shippingAddress)
                                <p class="font-medium text-gray-900">{{ $order->shippingAddress->recipient_name }}</p>
                                <p class="text-gray-600">{{ $order->shippingAddress->phone }}</p>
                                <p class="text-gray-600">{{ $order->shippingAddress->address_line_1 }}</p>
                                @if($order->shippingAddress->address_line_2)
                                    <p class="text-gray-600">{{ $order->shippingAddress->address_line_2 }}</p>
                                @endif
                                <p class="text-gray-600">{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->governorate }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                @if($order->notes)
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.track_order.notes') }}</h3>
                        <p class="text-gray-600">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
