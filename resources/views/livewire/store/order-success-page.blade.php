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
                    @php
                        $paymentIcons = [
                            'cod' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
                            'card' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>',
                            'vodafone_cash' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
                        ];
                        $paymentColors = [
                            'cod' => 'bg-amber-100 text-amber-600',
                            'card' => 'bg-blue-100 text-blue-600',
                            'vodafone_cash' => 'bg-red-100 text-red-600',
                            'orange_money' => 'bg-orange-100 text-orange-600',
                            'etisalat_cash' => 'bg-green-100 text-green-600',
                        ];
                        $paymentLabels = [
                            'cod' => __('messages.checkout.cash_on_delivery'),
                            'card' => __('messages.checkout.card_payment') ?? 'بطاقة ائتمان',
                            'vodafone_cash' => 'فودافون كاش',
                            'orange_money' => 'أورانج كاش',
                            'etisalat_cash' => 'اتصالات كاش',
                        ];
                        $paymentNotes = [
                            'cod' => __('messages.order_success.cod_note'),
                            'card' => __('messages.order_success.card_note') ?? 'تم الدفع بنجاح',
                            'vodafone_cash' => 'تم الدفع عبر المحفظة الإلكترونية',
                            'orange_money' => 'تم الدفع عبر المحفظة الإلكترونية',
                            'etisalat_cash' => 'تم الدفع عبر المحفظة الإلكترونية',
                        ];
                        $method = $order->payment_method ?? 'cod';
                        $icon = $paymentIcons[$method] ?? $paymentIcons['card'];
                        $colorClass = $paymentColors[$method] ?? 'bg-gray-100 text-gray-600';
                        $label = $paymentLabels[$method] ?? ucfirst($method);
                        $note = $paymentNotes[$method] ?? '';
                    @endphp
                    <div class="w-10 h-10 {{ $colorClass }} rounded-full flex items-center justify-center">
                        {!! $icon !!}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $label }}</p>
                        <p class="text-sm text-gray-500">{{ $note }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Guest CTA - Create Account --}}
        @guest('customer')
            <div style="background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #f59e0b 100%);" class="rounded p-4 p-md-5 mb-4 text-center shadow-lg">
                <div class="mx-auto" style="max-width: 700px;">
                    <h2 class="h3 fw-bold text-white mb-3">
                        🎁 {{ __('messages.order_success.create_account_title') ?? 'Track Your Orders Anytime!' }}
                    </h2>
                    <p class="text-white mb-4" style="opacity: 0.95;">
                        {{ __('messages.order_success.create_account_desc') ?? 'Create an account to easily track all your orders, save addresses, and enjoy a faster checkout next time!' }}
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center" style="gap: 16px;">
                        <a href="{{ route('register') }}?email={{ urlencode($order->guest_email) }}" 
                           class="btn btn-lg text-decoration-none" 
                           style="background-color: #fff; color: #7c3aed; font-weight: 700; padding: 12px 32px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: none; transition: all 0.3s; margin: 8px;">
                            <svg class="me-2" style="width: 20px; height: 20px; display: inline-block; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            {{ __('messages.order_success.create_account_btn') ?? 'Create Free Account' }}
                        </a>
                        <a href="{{ route('track-order') }}" 
                           class="btn btn-lg text-decoration-none"
                           style="background-color: rgba(255,255,255,0.2); color: #fff; font-weight: 600; padding: 12px 32px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.4); backdrop-filter: blur(10px); transition: all 0.3s; margin: 8px;">
                            <svg class="me-2" style="width: 20px; height: 20px; display: inline-block; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            {{ __('messages.order_success.track_order_btn') ?? 'Track Order' }}
                        </a>
                    </div>
                    <p class="text-white small mt-3 mb-0" style="opacity: 0.85;">
                        💡 {{ __('messages.order_success.migration_note') ?? 'Your current order will be automatically linked to your new account!' }}
                    </p>
                </div>
            </div>
        @endguest

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
