<div class="min-h-screen bg-gradient-to-b from-violet-50 to-white py-8 md:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Combo Header --}}
        <div class="text-center mb-10">
            @if($combo->image_path)
                <div class="mb-6 rounded-2xl overflow-hidden shadow-lg max-w-2xl mx-auto">
                    <img src="{{ asset('storage/' . $combo->image_path) }}"
                         alt="{{ $combo->name }}"
                         class="w-full h-auto object-cover">
                </div>
            @endif
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                {{ $combo->name }}
            </h1>
            @if($combo->description)
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    {{ $combo->description }}
                </p>
            @endif
        </div>

        {{-- Global Error --}}
        @if($globalError)
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-center font-medium">
                {{ $globalError }}
            </div>
        @endif

        {{-- Tier Selector --}}
        @if(count($tiers) > 1)
            <div class="mb-10 max-w-3xl mx-auto">
                <h2 class="text-xl font-bold text-gray-900 mb-4 text-center">اختر العرض المناسب لك</h2>
                <div class="grid grid-cols-1 sm:grid-cols-{{ min(count($tiers), 3) }} gap-4">
                    @foreach($tiers as $index => $tier)
                        <button
                            type="button"
                            wire:click="selectTier({{ $index }})"
                            @class([
                                'p-5 rounded-2xl border-2 text-center transition-all duration-200 cursor-pointer',
                                'border-violet-600 bg-violet-50 ring-2 ring-violet-200 shadow-md transform scale-105' => $selectedTierIndex === $index,
                                'border-gray-200 bg-white hover:border-violet-400 hover:bg-gray-50' => $selectedTierIndex !== $index,
                            ])
                        >
                            <div class="text-lg font-bold text-gray-900 mb-1">شراء {{ $tier['quantity'] }} قطع</div>
                            <div class="text-violet-700 font-bold text-2xl mb-2">
                                @if($tier['discount_type'] === 'fixed_price')
                                    بسعر {{ number_format($tier['fixed_price'], 0) }} ج.م
                                @else
                                    بخصم {{ $tier['discount_percentage'] }}%
                                @endif
                            </div>
                            @if($selectedTierIndex === $index)
                                <div class="mx-auto flex items-center justify-center w-6 h-6 rounded-full bg-violet-600 text-white mt-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Condition Sections --}}
        <div class="space-y-8 mb-10">
            @foreach($conditionData as $conditionId => $data)

                {{-- ═══════════════════════════════════════════════════════════
                     PRODUCT-TYPE CONDITION — fixed product, choose variant only
                ═══════════════════════════════════════════════════════════ --}}
                @if($data['type'] === 'product')
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden
                        {{ isset($errors[$conditionId]) ? 'ring-2 ring-red-300' : '' }}">

                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold text-gray-900">{{ $data['product_name'] }}</h2>
                                @if($data['required_quantity'] > 1)
                                    <span class="text-sm font-medium text-violet-600 bg-violet-50 px-3 py-1 rounded-full">
                                        الكمية: {{ $data['required_quantity'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-6">
                            {{-- Product info --}}
                            <div class="flex items-start gap-4 mb-4">
                                <img src="{{ $data['product_image'] }}"
                                     alt="{{ $data['product_name'] }}"
                                     class="w-20 h-20 object-contain bg-gray-50 rounded-lg border border-gray-200 flex-shrink-0">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $data['product_name'] }}</p>
                                    @if($data['is_on_sale'] ?? false)
                                        <p class="text-sm">
                                            <span class="text-gray-400 line-through">{{ number_format($data['regular_price'], 2) }} ج.م</span>
                                            <span class="text-red-600 font-semibold me-1">{{ number_format($data['product_price'], 2) }} ج.م</span>
                                            <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded-full">خصم</span>
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-500">{{ number_format($data['product_price'], 2) }} ج.م</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Variant buttons --}}
                            @if($data['has_variants'])
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">اختر النوع:</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        @foreach($data['variants'] as $variant)
                                            <button
                                                type="button"
                                                wire:click="selectVariant({{ $conditionId }}, {{ $variant['id'] }})"
                                                @class([
                                                    'px-4 py-3 border-2 rounded-lg text-sm font-medium transition-all duration-200',
                                                    'border-violet-600 bg-violet-50 text-violet-700 ring-2 ring-violet-200' => ($selections[$conditionId]['variant_id'] ?? null) === $variant['id'],
                                                    'border-gray-300 text-gray-700 hover:border-violet-400 hover:bg-gray-50' => ($selections[$conditionId]['variant_id'] ?? null) !== $variant['id'] && $variant['stock'] > 0,
                                                    'border-gray-200 text-gray-400 cursor-not-allowed opacity-50' => $variant['stock'] <= 0,
                                                ])
                                                {{ $variant['stock'] <= 0 ? 'disabled' : '' }}
                                            >
                                                <div class="flex flex-col items-center gap-1">
                                                    <span>{{ $variant['name'] }}</span>
                                                    @if($variant['stock'] <= 0)
                                                        <span class="text-xs text-red-500">نفذ من المخزون</span>
                                                    @elseif($variant['stock'] <= 5)
                                                        <span class="text-xs text-orange-500">متبقي {{ $variant['stock'] }}</span>
                                                    @endif
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Error --}}
                            @if(isset($errors[$conditionId]))
                                <div class="mt-3 text-sm text-red-600 font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $errors[$conditionId] }}
                                </div>
                            @endif
                        </div>
                    </div>

                {{-- ═══════════════════════════════════════════════════════════
                     CATEGORY-TYPE CONDITION — Mix & Match: one slot per unit
                ═══════════════════════════════════════════════════════════ --}}
                @elseif($data['type'] === 'category')
                    @php
                        $slotCount = $data['required_quantity'];
                        $categorySlots = $selections[$conditionId] ?? [];
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold text-gray-900">اختر من: {{ $data['category_name'] }}</h2>
                                @if($slotCount > 1)
                                    <span class="text-sm font-medium text-violet-600 bg-violet-50 px-3 py-1 rounded-full">
                                        {{ $slotCount }} قطع
                                    </span>
                                @endif
                            </div>
                            @if($slotCount > 1)
                                <p class="text-xs text-gray-500 mt-1">يمكنك اختيار منتجات مختلفة لكل قطعة (Mix & Match)</p>
                            @endif
                        </div>

                        <div class="divide-y divide-gray-100">
                            @for($slot = 0; $slot < $slotCount; $slot++)
                                @php
                                    $slotData       = $categorySlots[$slot] ?? ['product_id' => null, 'variant_id' => null];
                                    $slotError      = $errors["{$conditionId}.{$slot}"] ?? null;
                                    $selectedProdId = $slotData['product_id'];
                                    $selectedProd   = $selectedProdId
                                        ? collect($data['products'])->firstWhere('id', $selectedProdId)
                                        : null;
                                @endphp

                                <div class="p-6 {{ $slotError ? 'bg-red-50/40' : '' }}">

                                    {{-- Slot header (only when multiple slots) --}}
                                    @if($slotCount > 1)
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="w-7 h-7 rounded-full bg-violet-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                                                {{ $slot + 1 }}
                                            </span>
                                            <span class="text-sm font-semibold text-gray-700">القطعة {{ $slot + 1 }}</span>
                                            @if($selectedProd)
                                                <span class="text-xs text-violet-600 bg-violet-50 px-2 py-0.5 rounded-full font-medium ms-auto">
                                                    ✓ {{ $selectedProd['name'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Product grid --}}
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-3">اختر المنتج:</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            @foreach($data['products'] as $product)
                                                <button
                                                    type="button"
                                                    wire:click="selectProductForSlot({{ $conditionId }}, {{ $slot }}, {{ $product['id'] }})"
                                                    @class([
                                                        'flex items-center gap-3 p-3 border-2 rounded-lg transition-all duration-200 text-start',
                                                        'border-violet-600 bg-violet-50 ring-2 ring-violet-200' => $selectedProdId === $product['id'],
                                                        'border-gray-300 hover:border-violet-400 hover:bg-gray-50' => $selectedProdId !== $product['id'],
                                                    ])
                                                >
                                                    <img src="{{ $product['image'] }}"
                                                         alt="{{ $product['name'] }}"
                                                         class="w-14 h-14 object-contain bg-white rounded border border-gray-200 flex-shrink-0">
                                                    <div class="min-w-0">
                                                        <p class="font-medium text-gray-900 text-sm truncate">{{ $product['name'] }}</p>
                                                        @if($product['is_on_sale'] ?? false)
                                                            <p class="text-xs">
                                                                <span class="text-gray-400 line-through">{{ number_format($product['regular_price'], 2) }} ج.م</span>
                                                                <span class="text-red-600 font-semibold ms-1">{{ number_format($product['price'], 2) }} ج.م</span>
                                                            </p>
                                                        @else
                                                            <p class="text-sm text-gray-500">{{ number_format($product['price'], 2) }} ج.م</p>
                                                        @endif
                                                    </div>
                                                    @if($selectedProdId === $product['id'])
                                                        <span class="ms-auto text-violet-600 flex-shrink-0">
                                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Variant picker for selected product in this slot --}}
                                    @if($selectedProd && $selectedProd['has_variants'])
                                        <div class="mt-4 pt-4 border-t border-gray-100">
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">اختر النوع:</label>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                                @foreach($selectedProd['variants'] as $variant)
                                                    <button
                                                        type="button"
                                                        wire:click="selectVariantForSlot({{ $conditionId }}, {{ $slot }}, {{ $variant['id'] }})"
                                                        @class([
                                                            'px-4 py-3 border-2 rounded-lg text-sm font-medium transition-all duration-200',
                                                            'border-violet-600 bg-violet-50 text-violet-700 ring-2 ring-violet-200' => ($slotData['variant_id'] ?? null) === $variant['id'],
                                                            'border-gray-300 text-gray-700 hover:border-violet-400 hover:bg-gray-50' => ($slotData['variant_id'] ?? null) !== $variant['id'] && $variant['stock'] > 0,
                                                            'border-gray-200 text-gray-400 cursor-not-allowed opacity-50' => $variant['stock'] <= 0,
                                                        ])
                                                        {{ $variant['stock'] <= 0 ? 'disabled' : '' }}
                                                    >
                                                        <div class="flex flex-col items-center gap-1">
                                                            <span>{{ $variant['name'] }}</span>
                                                            @if($variant['stock'] <= 0)
                                                                <span class="text-xs text-red-500">نفذ من المخزون</span>
                                                            @elseif($variant['stock'] <= 5)
                                                                <span class="text-xs text-orange-500">متبقي {{ $variant['stock'] }}</span>
                                                            @endif
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Slot error --}}
                                    @if($slotError)
                                        <div class="mt-3 text-sm text-red-600 font-medium flex items-center gap-1">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $slotError }}
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>
                @endif

            @endforeach
        </div>

        {{-- Price Summary + CTA --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8 sticky bottom-4">
            {{-- Price Display --}}
            <div class="text-center mb-6">
                @if($originalPrice > $comboPrice)
                    <div class="flex items-center justify-center gap-3 mb-1">
                        <span class="text-xl text-gray-400 line-through">
                            {{ number_format($originalPrice, 2) }} ج.م
                        </span>
                        <span class="bg-red-100 text-red-700 text-sm font-bold px-3 py-1 rounded-full flex flex-wrap items-center justify-center gap-1">
                            @if(isset($tiers[$selectedTierIndex]) && $tiers[$selectedTierIndex]['discount_type'] === 'percentage')
                                <span class="whitespace-nowrap" dir="ltr">خصم {{ $tiers[$selectedTierIndex]['discount_percentage'] }}%</span>
                            @else
                                <span class="whitespace-normal break-words text-xs sm:text-sm">وفّر</span>
                                <span class="whitespace-nowrap text-xs sm:text-sm" dir="ltr">{{ number_format($originalPrice - $comboPrice, 2) }} ج.م</span>
                            @endif
                        </span>
                    </div>
                @endif
                <div class="text-4xl font-bold text-violet-700">
                    {{ number_format($comboPrice, 2) }} ج.م
                </div>
                <p class="text-sm text-gray-500 mt-1">السعر الإجمالي للعرض</p>
            </div>

            {{-- CTA Buttons --}}
            <div class="space-y-3">
                {{-- Add to Cart --}}
                <button
                    type="button"
                    wire:click="addAllToCart"
                    wire:loading.attr="disabled"
                    wire:target="addAllToCart,buyNow"
                    class="w-full flex items-center justify-center gap-3 px-8 py-4 bg-violet-600 hover:bg-violet-700 text-white font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="addAllToCart">
                        <svg class="w-6 h-6 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        أضف الكل إلى السلة
                    </span>
                    <span wire:loading wire:target="addAllToCart">
                        <svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        جاري الإضافة...
                    </span>
                </button>

                {{-- Buy Now --}}
                <button
                    type="button"
                    wire:click="buyNow"
                    wire:loading.attr="disabled"
                    wire:target="addAllToCart,buyNow"
                    class="w-full flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="buyNow">
                        <svg class="w-6 h-6 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        اشتري الآن
                    </span>
                    <span wire:loading wire:target="buyNow">
                        <svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        جاري المعالجة...
                    </span>
                </button>
            </div>
        </div>

        {{-- Offer validity --}}
        @if($combo->ends_at)
            <div class="text-center mt-6 text-sm text-gray-500">
                العرض ساري حتى {{ $combo->ends_at->format('Y/m/d') }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
{{-- Facebook Pixel: ViewContent on page load --}}
<script>
    (function() {
        function fireViewContent() {
            if (typeof fbq !== 'undefined') {
                fbq('track', 'ViewContent', {
                    content_name: @json($combo->name),
                    content_ids: @json($this->getProductIds()),
                    content_type: 'product',
                    value: {{ $comboPrice }},
                    currency: 'EGP'
                });
            }
        }
        var attempts = 0;
        var interval = setInterval(function() {
            attempts++;
            if (typeof fbq !== 'undefined') {
                clearInterval(interval);
                fireViewContent();
            } else if (attempts > 20) {
                clearInterval(interval);
            }
        }, 500);
    })();

    // AddToCart / BuyNow event listener — dispatched from Livewire on success
    window.addEventListener('combo-added', event => {
        const data = event.detail[0];

        if (typeof fbq !== 'undefined') {
            // Always fire AddToCart
            fbq('track', 'AddToCart', {
                content_name: data.combo_name,
                content_ids: data.content_ids,
                contents: data.contents,
                content_type: 'product',
                value: data.value,
                currency: data.currency,
                num_items: data.num_items
            });

            // If Buy Now, also fire InitiateCheckout
            if (data.action === 'buy_now') {
                fbq('track', 'InitiateCheckout', {
                    content_name: data.combo_name,
                    content_ids: data.content_ids,
                    contents: data.contents,
                    content_type: 'product',
                    value: data.value,
                    currency: data.currency,
                    num_items: data.num_items
                });
            }
        }

        // JS redirect ensures pixel fires before navigation (300ms buffer)
        setTimeout(() => {
            window.location.href = data.redirect_url;
        }, 300);
    });
</script>
@endpush

