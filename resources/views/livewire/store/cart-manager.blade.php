{{-- Cart Manager - Slide-over Mini Cart --}}
<div wire:id="cart-manager">
    {{-- Backdrop Overlay --}}
    <div 
        x-show="$wire.isOpen" 
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="$wire.isOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-40"
        x-cloak
    ></div>

    {{-- Slide-over Panel --}}
    <div 
        x-show="$wire.isOpen"
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 max-w-full flex z-50"
        x-cloak
    >
        <div class="w-screen max-w-md">
            <div class="h-full flex flex-col bg-white shadow-xl">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-violet-600 to-violet-800 text-white">
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h2 class="text-xl font-bold">سلة التسوق</h2>
                        @if($cartCount > 0)
                            <span class="bg-white text-violet-600 text-xs font-bold rounded-full px-2 py-1">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </div>
                    <button 
                        @click="$wire.isOpen = false"
                        class="p-1 hover:bg-white/20 rounded-lg transition"
                        title="إغلاق"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Cart Items --}}
                <div class="flex-1 overflow-y-auto px-6 py-4">
                    @if($cart && $cart->items->count() > 0)
                        <div class="space-y-4">
                            @foreach($cart->items as $item)
                                <div class="flex gap-4 p-4 bg-gray-50 rounded-lg" wire:key="cart-item-{{ $item->id }}">
                                    {{-- Product Image --}}
                                    <div class="w-20 h-20 flex-shrink-0 bg-white rounded-lg overflow-hidden border border-gray-200">
                                        @php
                                            $primaryMedia = $item->product->getMedia('product-images')->first();
                                            $imagePath = $primaryMedia 
                                                ? ($primaryMedia->hasGeneratedConversion('thumbnail') 
                                                    ? $primaryMedia->getUrl('thumbnail') 
                                                    : $primaryMedia->getUrl())
                                                : asset('images/default-product.png');
                                        @endphp
                                        <img 
                                            src="{{ $imagePath }}" 
                                            alt="{{ $item->product->name }}"
                                            class="w-full h-full object-cover"
                                        >
                                    </div>

                                    {{-- Product Info --}}
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">
                                            {{ $item->product->name }}
                                        </h3>
                                        
                                        @if($item->variant)
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $item->variant->name }}
                                            </p>
                                        @endif

                                        <div class="flex items-center justify-between mt-2">
                                            {{-- Quantity Controls --}}
                                            <div class="flex items-center gap-2">
                                                {{-- Decrease Button --}}
                                                <button 
                                                    wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                                    @disabled($item->quantity <= 1)
                                                    class="w-7 h-7 flex items-center justify-center bg-white border border-gray-300 rounded hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                    </svg>
                                                </button>
                                                
                                                {{-- Quantity Display --}}
                                                <span class="w-8 text-center font-semibold text-gray-900">
                                                    {{ $item->quantity }}
                                                </span>
                                                
                                                {{-- Increase Button --}}
                                                <button 
                                                    wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                                    class="w-7 h-7 flex items-center justify-center bg-white border border-gray-300 rounded hover:bg-gray-100 transition"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>

                                            {{-- Price --}}
                                            <div class="text-right">
                                                <p class="text-sm font-bold text-violet-600">
                                                    {{ number_format($item->price * $item->quantity, 2) }} ر.س
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Remove Button (with loading state) --}}
                                        <button 
                                            wire:click="removeItem({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="removeItem"
                                            class="mt-2 text-xs text-red-600 hover:text-red-800 font-medium flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <svg wire:loading.remove wire:target="removeItem" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <svg wire:loading wire:target="removeItem" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="removeItem">إزالة</span>
                                            <span wire:loading wire:target="removeItem">جاري الحذف...</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Clear Cart Button --}}
                        @if($cart->items->count() > 1)
                            <button 
                                wire:click="clearCart"
                                wire:confirm="هل أنت متأكد من تفريغ السلة؟"
                                class="mt-4 w-full py-2 text-sm text-red-600 hover:text-red-800 font-medium border border-red-300 rounded-lg hover:bg-red-50 transition"
                            >
                                تفريغ السلة
                            </button>
                        @endif
                    @else
                        {{-- Empty Cart State --}}
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">السلة فارغة</h3>
                            <p class="text-sm text-gray-500 mb-6">ابدأ بإضافة منتجات إلى سلتك</p>
                            <a 
                                href="{{ route('products.index') }}" 
                                wire:navigate
                                class="inline-flex items-center gap-2 px-6 py-3 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                تصفح المنتجات
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Footer: Subtotal & Checkout --}}
                @if($cart && $cart->items->count() > 0)
                    <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                        {{-- Subtotal --}}
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-base font-semibold text-gray-700">المجموع الفرعي:</span>
                            <span class="text-2xl font-bold text-violet-600">
                                {{ number_format($subtotal, 2) }} ر.س
                            </span>
                        </div>

                        <p class="text-xs text-gray-500 mb-4 text-center">
                            الشحن والضرائب سيتم حسابها عند الدفع
                        </p>

                        {{-- Action Buttons --}}
                        <div class="space-y-3">
                            <a 
                                href="{{ route('checkout') }}" 
                                wire:navigate
                                class="block w-full py-3 bg-gradient-to-r from-violet-600 to-violet-800 text-white text-center font-bold rounded-lg hover:from-violet-700 hover:to-violet-900 transition shadow-lg"
                            >
                                إتمام الطلب
                            </a>

                            <a 
                                href="{{ route('cart') }}" 
                                wire:navigate
                                class="block w-full py-3 bg-white text-violet-600 text-center font-semibold border-2 border-violet-600 rounded-lg hover:bg-violet-50 transition"
                            >
                                عرض السلة الكاملة
                            </a>

                            <button 
                                wire:click="closeCart"
                                class="block w-full py-3 text-gray-600 text-center font-medium hover:text-gray-800 transition"
                            >
                                متابعة التسوق
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
