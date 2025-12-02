<div class="min-h-screen bg-cream-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Success Icon & Message --}}
        <div class="text-center mb-10">
            <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                {{ __('messages.order_success.thank_you') }}
            </h1>
            <p class="text-lg text-gray-600">
                {{ __('messages.order_success.confirmation_sent') }}
            </p>
        </div>

        {{-- Order Details Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            {{-- Header --}}
            <div class="bg-violet-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-violet-200 text-sm">{{ __('messages.order_success.order_number') }}</p>
                        <p class="text-white font-bold text-lg">{{ $order->order_number }}</p>
                    </div>
                    <div class="text-end">
                        <p class="text-violet-200 text-sm">{{ __('messages.order_success.order_date') }}</p>
                        <p class="text-white font-medium">{{ $order->created_at->format('M d, Y - h:i A') }}</p>
                    </div>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('messages.order_success.items_ordered') }}</h3>
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4">
                            {{-- Product Image --}}
                            <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                @if($item->product && $item->product->getFirstMediaUrl('products'))
                                    <img src="{{ $item->product->getFirstMediaUrl('products', 'thumb') }}" 
                                         alt="{{ $item->product_name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Product Details --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ $item->product_name }}</p>
                                @if($item->variant_name)
                                    <p class="text-sm text-gray-500">{{ $item->variant_name }}</p>
                                @endif
                                <p class="text-sm text-gray-500">{{ __('messages.order_success.qty') }}: {{ $item->quantity }}</p>
                            </div>
                            
                            {{-- Price --}}
                            <div class="text-end">
                                <p class="font-semibold text-gray-900">{{ number_format($item->subtotal, 2) }} {{ __('messages.currency') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="p-6 border-b border-gray-100 bg-gray-50">
                <div class="space-y-2">
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __('messages.checkout.subtotal') }}</span>
                        <span>{{ number_format($order->subtotal, 2) }} {{ __('messages.currency') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __('messages.checkout.shipping') }}</span>
                        <span>{{ number_format($order->shipping_cost, 2) }} {{ __('messages.currency') }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>{{ __('messages.order_success.discount') }}</span>
                            <span>-{{ number_format($order->discount_amount, 2) }} {{ __('messages.currency') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-200">
                        <span>{{ __('messages.checkout.total') }}</span>
                        <span>{{ number_format($order->total, 2) }} {{ __('messages.currency') }}</span>
                    </div>
                </div>
            </div>

            {{-- Shipping Address --}}
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 mb-3">{{ __('messages.order_success.shipping_to') }}</h3>
                @if($order->shippingAddress)
                    <div class="text-gray-600">
                        <p class="font-medium text-gray-900">{{ $order->shippingAddress->full_name }}</p>
                        <p>{{ $order->shippingAddress->phone }}</p>
                        <p>{{ $order->shippingAddress->street_address }}</p>
                        <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->governorate }}</p>
                    </div>
                @else
                    <div class="text-gray-600">
                        <p class="font-medium text-gray-900">{{ $order->guest_name }}</p>
                        <p>{{ $order->guest_phone }}</p>
                        <p>{{ $order->guest_address }}</p>
                        <p>{{ $order->guest_city }}, {{ $order->guest_governorate }}</p>
                    </div>
                @endif
            </div>

            {{-- Payment Method --}}
            <div class="p-6">
                <h3 class="font-semibold text-gray-900 mb-3">{{ __('messages.checkout.payment_method') }}</h3>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ __('messages.checkout.cash_on_delivery') }}</p>
                        <p class="text-sm text-gray-500">{{ __('messages.order_success.cod_note') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" 
               class="inline-flex items-center justify-center px-8 py-3 bg-violet-600 text-white font-semibold rounded-xl hover:bg-violet-700 transition-colors">
                <svg class="w-5 h-5 me-2 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                {{ __('messages.checkout.continue_shopping') }}
            </a>
            
            @auth
                <a href="{{ route('profile') }}" 
                   class="inline-flex items-center justify-center px-8 py-3 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    {{ __('messages.order_success.view_orders') }}
                </a>
            @endauth
        </div>

        {{-- Help Section --}}
        <div class="mt-10 text-center">
            <p class="text-gray-500 text-sm">
                {{ __('messages.order_success.help_text') }}
                <a href="mailto:support@violet.com" class="text-violet-600 hover:underline">support@violet.com</a>
            </p>
        </div>
    </div>
</div>
