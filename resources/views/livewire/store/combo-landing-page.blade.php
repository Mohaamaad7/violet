<div class="min-h-screen bg-gradient-to-b from-violet-50 to-white py-8 md:py-12">
    {{-- Protocol 2: New Alpine Toast Component & Auto-scroll Listener --}}
    <div x-data="{ toasts: [] }"
         x-on:show-toast.window="
            const toast = $event.detail;
            toasts.push(toast);
            setTimeout(() => toasts.shift(), 4000);
         "
         x-on:scroll-to-missing.window="
            const el = document.getElementById($event.detail.targetId);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
         "
         class="fixed top-4 left-1/2 -translate-x-1/2 z-50 space-y-2 w-11/12 max-w-sm">
      <template x-for="toast in toasts" :key="toast.message">
        <div class="p-3 rounded-lg shadow-lg text-white text-sm font-medium"
             :class="toast.type === 'error' ? 'bg-red-600' : 'bg-green-600'"
             x-text="toast.message"></div>
      </template>
    </div>

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
                    {{-- Protocol 3: Add ID and scroll-mt-20 for auto-scroll --}}
                    <div id="piece-{{ $conditionId }}" 
                         class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden scroll-mt-20
                        {{ isset($errors[$conditionId]) ? 'ring-2 ring-red-300' : '' }}">

                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold text-gray-900">
                                    {{ $data['product_name'] }}
                                    @if($showScrollNudge && $this->getFirstUnselectedSlot() === (string)$conditionId)
                                        <span class="inline-block animate-bounce ml-2 text-violet-600">
                                            <svg class="w-5 h-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            <span class="text-xs">اختر أدناه</span>
                                        </span>
                                    @endif
                                </h2>
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
                                                    'px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 relative',
                                                    'border-[3px] border-violet-600 bg-violet-50 text-violet-700' => ($selections[$conditionId]['variant_id'] ?? null) === $variant['id'],
                                                    'border-2 border-gray-300 text-gray-700 hover:border-violet-400 hover:bg-gray-50' => ($selections[$conditionId]['variant_id'] ?? null) !== $variant['id'] && $variant['stock'] > 0,
                                                    'border-2 border-gray-200 text-gray-400 cursor-not-allowed opacity-50' => $variant['stock'] <= 0,
                                                ])
                                                {{ $variant['stock'] <= 0 ? 'disabled' : '' }}
                                            >
                                                @if(($selections[$conditionId]['variant_id'] ?? null) === $variant['id'])
                                                    <svg class="w-6 h-6 text-violet-600 absolute top-2 right-2" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" /></svg>
                                                @endif
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

                                {{-- Protocol 3: Add ID and scroll-mt-20 --}}
                                <div id="piece-{{ $conditionId }}-{{ $slot }}" class="p-6 scroll-mt-20 {{ $slotError ? 'bg-red-50/40' : '' }}">

                                    {{-- Slot header (only when multiple slots) --}}
                                    @if($slotCount > 1)
                                        {{-- Protocol 5: Prominent Piece Title --}}
                                        <div class="flex items-center gap-3 mb-4 pb-2 border-b border-gray-100">
                                            <span class="w-8 h-8 rounded-full bg-violet-100 text-violet-700 text-sm font-bold flex items-center justify-center flex-shrink-0">
                                                {{ $slot + 1 }}
                                            </span>
                                            <span class="text-lg font-bold text-gray-900">
                                                القطعة {{ $slot + 1 }}
                                                {{-- Protocol 2: Proactive Nudge --}}
                                                @if($showScrollNudge && $this->getFirstUnselectedSlot() === "{$conditionId}.{$slot}")
                                                    <span class="inline-block animate-bounce ml-2 text-violet-600">
                                                        <svg class="w-5 h-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                        <span class="text-xs">اختر أدناه</span>
                                                    </span>
                                                @endif
                                            </span>
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
                                                        'flex items-center gap-3 p-3 rounded-lg transition-all duration-200 text-start relative',
                                                        'border-[3px] border-violet-600 bg-violet-50' => $selectedProdId === $product['id'],
                                                        'border-2 border-gray-300 hover:border-violet-400 hover:bg-gray-50' => $selectedProdId !== $product['id'],
                                                    ])
                                                >
                                                    {{-- Protocol 4: Selected State Checkmark --}}
                                                    @if($selectedProdId === $product['id'])
                                                        <svg class="w-6 h-6 text-violet-600 absolute top-2 right-2" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" /></svg>
                                                    @endif
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
                                                            'px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 relative',
                                                            'border-[3px] border-violet-600 bg-violet-50 text-violet-700' => ($slotData['variant_id'] ?? null) === $variant['id'],
                                                            'border-2 border-gray-300 text-gray-700 hover:border-violet-400 hover:bg-gray-50' => ($slotData['variant_id'] ?? null) !== $variant['id'] && $variant['stock'] > 0,
                                                            'border-2 border-gray-200 text-gray-400 cursor-not-allowed opacity-50' => $variant['stock'] <= 0,
                                                        ])
                                                        {{ $variant['stock'] <= 0 ? 'disabled' : '' }}
                                                    >
                                                        @if(($slotData['variant_id'] ?? null) === $variant['id'])
                                                            <svg class="w-6 h-6 text-violet-600 absolute top-2 right-2" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" /></svg>
                                                        @endif
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
        {{-- Protocol 1 & 6: Compact sticky bar and ResizeObserver --}}
        <div class="bg-white rounded-t-2xl md:rounded-2xl shadow-[0_-8px_30px_rgb(0,0,0,0.06)] md:shadow-lg border-t md:border border-gray-100 p-3 md:p-8 sticky bottom-0 md:bottom-4 z-40"
             x-data
             x-init="
                const observer = new ResizeObserver(entries => {
                    for (let entry of entries) {
                        document.documentElement.style.setProperty('--sticky-bar-height', entry.target.offsetHeight + 'px');
                    }
                });
                observer.observe($el);
             ">
            {{-- Price Display --}}
            <div class="text-center mb-2 md:mb-6">
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
                <div class="text-2xl md:text-4xl font-bold text-violet-700">
                    {{ number_format($comboPrice, 2) }} ج.م
                </div>
                <p class="text-xs md:text-sm text-gray-500 mt-0.5 md:mt-1">السعر الإجمالي للعرض</p>
            </div>

            {{-- CTA Buttons --}}
            <div class="grid grid-cols-2 gap-2 md:flex md:flex-col md:space-y-3">
                {{-- Add to Cart --}}
                <button
                    type="button"
                    wire:click="addAllToCart"
                    wire:loading.attr="disabled"
                    wire:target="addAllToCart,buyNow"
                    class="w-full flex items-center justify-center gap-1 md:gap-3 px-2 py-3 md:px-8 md:py-4 bg-violet-600 hover:bg-violet-700 text-white font-bold text-sm md:text-lg rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="addAllToCart">
                        <svg class="w-5 h-5 md:w-6 md:h-6 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        <span class="md:hidden">أضف للسلة</span>
                        <span class="hidden md:inline">أضف الكل إلى السلة</span>
                    </span>
                    <span wire:loading wire:target="addAllToCart">
                        <svg class="animate-spin h-4 w-4 md:h-5 md:w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                    class="w-full flex items-center justify-center gap-1 md:gap-3 px-2 py-3 md:px-8 md:py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold text-sm md:text-lg rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="buyNow">
                        <svg class="w-5 h-5 md:w-6 md:h-6 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        اشتري الآن
                    </span>
                    <span wire:loading wire:target="buyNow">
                        <svg class="animate-spin h-4 w-4 md:h-5 md:w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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

    {{-- Protocol 6: Floating WhatsApp Button --}}
    <a href="https://wa.me/{{ env('WHATSAPP_NUMBER', '201091191056') }}?text={{ urlencode('مرحباً، أريد الاستفسار عن منتج: ' . $combo->name) }}"
       target="_blank"
       rel="noopener noreferrer"
       class="fixed left-6 z-30 flex items-center justify-center w-14 h-14 bg-green-500 text-white rounded-full shadow-lg p-3 hover:bg-green-600 transition-all duration-300 hover:scale-110 group"
       style="bottom: calc(var(--sticky-bar-height, 0px) + 16px)"
       aria-label="Contact on WhatsApp"
    >
        <svg class="w-8 h-8 transition-transform group-hover:rotate-12" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
        </svg>
        <span class="absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-40 animate-ping -z-10"></span>
    </a>
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

