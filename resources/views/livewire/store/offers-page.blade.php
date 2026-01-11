<div>
    {{-- Hero Banner --}}
    <section
        class="relative bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-500 py-16 md:py-24 overflow-hidden">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=" 60"
                height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" %3E%3Cg fill="none"
                fill-rule="evenodd" %3E%3Cg fill="%23ffffff" fill-opacity="1" %3E%3Cpath
                d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"
                /%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        {{-- Floating Icons --}}
        <div class="absolute top-10 left-10 text-white/20 text-6xl animate-bounce" style="animation-delay: 0s;">🎁</div>
        <div class="absolute top-20 right-20 text-white/20 text-5xl animate-bounce" style="animation-delay: 0.5s;">💰
        </div>
        <div class="absolute bottom-10 left-1/4 text-white/20 text-4xl animate-bounce" style="animation-delay: 1s;">🏷️
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center text-white">
                <span
                    class="inline-block bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-full text-sm font-bold mb-6 animate-pulse">
                    🔥 {{ __('messages.hot_deals') }}
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
                    {{ __('messages.offers_page_title') }}
                </h1>
                <p class="text-lg md:text-xl text-white/80 max-w-2xl mx-auto">
                    {{ __('messages.offers_page_subtitle') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Discount Codes Section --}}
    @if($discountCodes->count() > 0)
        <section class="py-12 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 bg-violet-100 rounded-full flex items-center justify-center text-2xl">🎫</div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.active_coupons') }}</h2>
                        <p class="text-gray-500">{{ __('messages.use_at_checkout') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($discountCodes as $code)
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group">
                            {{-- Top Accent --}}
                            <div class="h-2 bg-gradient-to-r from-violet-500 to-fuchsia-500"></div>

                            <div class="p-6">
                                {{-- Discount Badge --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        @if($code->discount_type === 'percentage')
                                            <span
                                                class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-bold">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 7h.01M17 17h.01M7 17L17 7m-7 0a2 2 0 11-4 0 2 2 0 014 0zm10 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                    </path>
                                                </svg>
                                                {{ number_format($code->discount_value, 0) }}% {{ __('messages.off') }}
                                            </span>
                                        @elseif($code->discount_type === 'fixed')
                                            <span
                                                class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-bold">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                                {{ number_format($code->discount_value, 0) }} {{ __('messages.currency') }}
                                            </span>
                                        @elseif($code->discount_type === 'free_shipping')
                                            <span
                                                class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm font-bold">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                                                    </path>
                                                </svg>
                                                {{ __('messages.free_shipping') }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($code->expires_at)
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            {{ __('messages.expires_on') }}: {{ $code->expires_at->format('d/m') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Code Display --}}
                                <div class="relative mb-4">
                                    <div
                                        class="flex items-center justify-between bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-4 group-hover:border-violet-400 transition-colors">
                                        <code class="text-xl font-bold text-gray-800 tracking-wider"
                                            id="code-{{ $code->id }}">{{ $code->code }}</code>
                                        <button onclick="copyCode('{{ $code->code }}', this)"
                                            class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 hover:scale-105">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">
                                                </path>
                                            </svg>
                                            <span class="copy-text">{{ __('messages.copy_code') }}</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Min Order Info --}}
                                @if($code->min_order_amount > 0)
                                    <p class="text-sm text-gray-500 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ __('messages.min_order') }}: {{ number_format($code->min_order_amount, 0) }}
                                        {{ __('messages.currency') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @else
        {{-- No Coupons Message --}}
        <section class="py-12 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="text-center py-8">
                    <div class="text-6xl mb-4">🎫</div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">{{ __('messages.no_coupons') }}</h3>
                    <p class="text-gray-500">{{ __('messages.check_back_later') }}</p>
                </div>
            </div>
        </section>
    @endif

    {{-- Sale Products Section --}}
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-end mb-10 border-b border-gray-200 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-2xl">🏷️</div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.discount_products') }}</h2>
                        <p class="text-gray-500">{{ __('messages.biggest_discounts') }}</p>
                    </div>
                </div>
                <a href="/products?on_sale=1"
                    class="text-violet-600 hover:text-violet-800 font-bold flex items-center gap-2 transition-colors duration-300 group">
                    <span>{{ __('messages.view_all') }}</span>
                    <span
                        class="transform group-hover:translate-x-1 {{ app()->getLocale() === 'ar' ? 'group-hover:-translate-x-1' : '' }} transition-transform">&rarr;</span>
                </a>
            </div>

            @if($saleProducts->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 gap-y-10">
                    @foreach($saleProducts as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">🏷️</div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">{{ __('messages.no_sale_products') }}</h3>
                    <p class="text-gray-500 mb-6">{{ __('messages.check_back_later') }}</p>
                    <a href="/products"
                        class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        {{ __('messages.browse_all_products') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </section>
</div>

@push('scripts')
    <script>
        function copyCode(code, button) {
            navigator.clipboard.writeText(code).then(() => {
                const textSpan = button.querySelector('.copy-text');
                const originalText = textSpan.textContent;

                // Change button appearance
                button.classList.remove('bg-violet-600', 'hover:bg-violet-700');
                button.classList.add('bg-green-500');
                textSpan.textContent = '{{ __('messages.code_copied') }}';

                // Show toast
                if (window.showToast) {
                    window.showToast('{{ __('messages.code_copied_success') }}', 'success');
                }

                // Reset after 2 seconds
                setTimeout(() => {
                    button.classList.remove('bg-green-500');
                    button.classList.add('bg-violet-600', 'hover:bg-violet-700');
                    textSpan.textContent = originalText;
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = code;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);

                if (window.showToast) {
                    window.showToast('{{ __('messages.code_copied_success') }}', 'success');
                }
            });
        }
    </script>
@endpush