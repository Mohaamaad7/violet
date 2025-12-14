{{-- Shopping Cart Page --}}
<div>
    {{-- Breadcrumbs --}}
    <div class="bg-gray-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <x-store.breadcrumbs :items="[
                ['label' => __('store.header.home'), 'url' => route('home')],
                ['label' => __('store.cart.shopping_cart'), 'url' => null]
            ]" />
        </div>
    </div>

    {{-- Cart Content --}}
    <div class="w-full mx-auto px-2 lg:px-4 py-8">
        @if($cart && $cart->items->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                {{-- Cart Items (Right in RTL) --}}
                <div class="lg:col-span-9">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        {{-- Header --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-violet-600 to-violet-800 text-white">
                            <div class="flex items-center justify-between flex-row-reverse">
                                <!-- Count Badge -->
                                <span class="flex items-center justify-center w-14 h-14 rounded-full bg-white/20 text-white text-xl font-bold shadow-md">
                                    {{ $cartCount }}
                                    <span class="text-base font-normal ml-1">{{ $cartCount === 1 ? __('store.cart.product') : __('store.cart.products') }}</span>
                                </span>
                                <!-- Title and Icon -->
                                <div class="flex items-center gap-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <h1 class="text-2xl font-bold">{{ __('store.cart.shopping_cart') }}</h1>
                                </div>
                                @if($cart->items->count() > 1)
                                    <button 
                                        wire:click="clearCart"
                                        wire:confirm="{{ __('store.cart.clear_cart_confirm') }}"
                                        class="text-sm text-white/90 hover:text-white font-medium border border-white/50 hover:border-white px-4 py-2 rounded-lg transition"
                                    >
                                        {{ __('store.cart.clear_cart') }}
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Cart Items List --}}
                        <div class="divide-y divide-gray-200">
                            @foreach($cart->items as $item)
                                <div class="p-6 hover:bg-gray-50 transition" wire:key="cart-item-{{ $item->id }}">
                                    <div class="flex gap-4">
                                        {{-- Product Image --}}
                                        <div class="w-24 h-24 flex-shrink-0 bg-white rounded-lg overflow-hidden border-2 border-gray-200">
                                            @php
                                                $primaryMedia = $item->product->getMedia('product-images')->first();
                                                $imagePath = $primaryMedia 
                                                    ? ($primaryMedia->hasGeneratedConversion('thumbnail') 
                                                        ? $primaryMedia->getUrl('thumbnail') 
                                                        : $primaryMedia->getUrl())
                                                    : asset('images/default-product.svg');
                                            @endphp
                                            <a href="{{ route('product.show', $item->product->slug) }}" wire:navigate>
                                                <img 
                                                    src="{{ $imagePath }}" 
                                                    alt="{{ $item->product->name }}"
                                                    class="w-full h-full object-cover hover:scale-110 transition-transform duration-300"
                                                >
                                            </a>
                                        </div>

                                        {{-- Product Details --}}
                                        <div class="flex-1 min-w-0">
                                            {{-- Product Name --}}
                                            <a 
                                                href="{{ route('product.show', $item->product->slug) }}" 
                                                wire:navigate
                                                class="text-lg font-semibold text-gray-900 hover:text-violet-600 transition"
                                            >
                                                {{ $item->product->name }}
                                            </a>

                                            {{-- Variant Info --}}
                                            @if($item->variant)
                                                <p class="text-sm text-gray-500 mt-1">
                                                    <span class="font-medium">{{ __('store.cart.option') }}:</span> {{ $item->variant->name }}
                                                </p>
                                            @endif

                                            {{-- Category --}}
                                            @if($item->product->category)
                                                <p class="text-xs text-gray-400 mt-1">
                                                    {{ $item->product->category->name }}
                                                </p>
                                            @endif

                                            {{-- Price & Quantity Controls --}}
                                            <div class="flex items-center justify-between mt-4">
                                                {{-- Quantity Controls --}}
                                                <div class="flex items-center gap-3">
                                                    <span class="text-sm text-gray-600 font-medium">{{ __('store.cart.quantity') }}:</span>
                                                    <div class="flex items-center gap-2">
                                                        <button 
                                                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                                            class="w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-gray-300 rounded-lg transition"
                                                            {{ $item->quantity <= 1 ? 'disabled' : '' }}
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                            </svg>
                                                        </button>
                                                        
                                                        <input 
                                                            type="number" 
                                                            wire:model.lazy="item.quantity"
                                                            wire:change="updateQuantity({{ $item->id }}, $event.target.value)"
                                                            class="w-16 text-center font-semibold text-gray-900 border-2 border-gray-300 rounded-lg focus:border-violet-500 focus:ring-2 focus:ring-violet-200 focus:outline-none"
                                                            min="1"
                                                            value="{{ $item->quantity }}"
                                                        >
                                                        
                                                        <button 
                                                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                                            class="w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-gray-300 rounded-lg transition"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Item Total Price --}}
                                                <div class="text-right">
                                                    <p class="text-sm text-gray-500">{{ __('store.cart.price') }}</p>
                                                    <p class="text-xl font-bold text-violet-600">
                                                        {{ number_format($item->price * $item->quantity, 2) }} ر.س
                                                    </p>
                                                    @if($item->quantity > 1)
                                                        <p class="text-xs text-gray-400">
                                                            ({{ number_format($item->price, 2) }} × {{ $item->quantity }})
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Remove Button --}}
                                            <button 
                                                wire:click="removeItem({{ $item->id }})"
                                                class="mt-3 text-sm text-red-600 hover:text-red-800 font-medium flex items-center gap-1"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                {{ __('store.cart.remove') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Continue Shopping --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <a 
                                href="{{ route('products.index') }}" 
                                wire:navigate
                                class="inline-flex items-center gap-2 text-violet-600 hover:text-violet-800 font-medium transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                {{ __('store.cart.continue_shopping') }}
            </a>
                        </div>
                    </div>
                </div>

                {{-- Order Summary (Left in RTL) --}}
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-24">
                        {{-- Header --}}
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('store.cart.order_summary') }}</h2>
                        </div>

                        {{-- Summary Details --}}
                        <div class="px-6 py-4 space-y-4">
                            {{-- Subtotal --}}
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">{{ __('store.cart.subtotal') }}</span>
                                <span class="font-semibold text-gray-900">{{ number_format($subtotal, 2) }} ر.س</span>
                            </div>

                            {{-- Shipping --}}
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">{{ __('store.cart.shipping') }}</span>
                                <span class="font-semibold {{ $shippingCost === 0 ? 'text-green-600' : 'text-gray-900' }}">
                                    @if($shippingCost === 0)
                                        {{ __('store.cart.free') }} 🎉
                                    @else
                                        {{ number_format($shippingCost, 2) }} {{ __('store.currency.sar') }}
                                    @endif
                                </span>
                            </div>

                            @if($subtotal < 200 && $subtotal > 0)
                                <div class="text-xs text-gray-500 bg-blue-50 border border-blue-200 rounded p-2">
                                    💡 أضف {{ number_format(200 - $subtotal, 2) }} ر.س للحصول على شحن مجاني
                                </div>
                            @endif

                            {{-- Tax (VAT 15%) --}}
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">{{ __('store.cart.tax') }}</span>
                                <span class="font-semibold text-gray-900">{{ number_format($taxAmount, 2) }} ر.س</span>
                            </div>

                            {{-- Divider --}}
                            <div class="border-t-2 border-gray-200 pt-4">
                                {{-- Total --}}
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-gray-900">{{ __('store.cart.total') }}</span>
                                    <span class="text-2xl font-bold text-violet-600">{{ number_format($total, 2) }} ر.س</span>
                                </div>
                            </div>
                        </div>

                        {{-- Checkout Button --}}
                        <div class="px-6 pb-6">
                            <a 
                                href="{{ route('checkout') }}" 
                                wire:navigate
                                class="block w-full py-4 bg-gradient-to-r from-violet-600 to-violet-800 text-white text-center text-lg font-bold rounded-lg hover:from-violet-700 hover:to-violet-900 transition shadow-lg"
                            >
                                {{ __('store.cart.checkout') }}
                            </a>

                            <p class="text-xs text-gray-500 text-center mt-3">
                                {{ __('store.cart.secure_payment') }} 🔒
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Empty Cart State --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                    <svg class="w-32 h-32 text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">{{ __('store.cart.empty') }}</h2>
                    <p class="text-gray-500 mb-8 max-w-md">
                        {{ __('store.cart.empty_desc') }}
                    </p>
                    <a 
                        href="{{ route('products.index') }}" 
                        wire:navigate
                        class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-violet-600 to-violet-800 text-white text-lg font-bold rounded-lg hover:from-violet-700 hover:to-violet-900 transition shadow-lg"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ __('store.cart.browse_products') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
