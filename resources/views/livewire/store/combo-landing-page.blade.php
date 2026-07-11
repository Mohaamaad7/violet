<div class="min-h-screen bg-gradient-to-b from-violet-50 to-white py-6 md:py-12">
    {{-- Toast Notification Stack --}}
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

    {{-- ─── Main Content Container (pb-44 clears the fixed sticky bar) ─── --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-44">

        {{-- Combo Header --}}
        <div class="text-center mb-8">
            @if($combo->image_path)
                <div class="mb-5 rounded-2xl overflow-hidden shadow-lg max-w-2xl mx-auto">
                    <img src="{{ asset('storage/' . $combo->image_path) }}"
                         alt="{{ $combo->name }}"
                         class="w-full h-auto object-cover">
                </div>
            @endif
            <h1 class="text-2xl md:text-4xl font-bold text-gray-900 mb-3">
                {{ $combo->name }}
            </h1>
            @if($combo->description)
                <div class="text-base text-gray-600 max-w-2xl mx-auto prose prose-violet">
                    {!! $combo->description !!}
                </div>
            @endif
        </div>

        {{-- Global Error --}}
        @if($globalError)
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-center font-medium text-sm">
                {{ $globalError }}
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════
             TIER SELECTOR — 2-column grid, index-0 (highest quantity = best
             value) spans full row via col-span-2.
             Rationale: ComboRule has no is_featured column; PHP sorts tiers by
             quantity DESC so $index===0 is always the "best deal" tier.
        ═══════════════════════════════════════════════════════════════════ --}}
        @if(count($tiers) > 1)
            <div class="mb-8">
                <h2 class="text-lg font-bold text-gray-900 mb-3 text-center">اختر العرض المناسب لك</h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($tiers as $index => $tier)
                        <button
                            type="button"
                            wire:click="selectTier({{ $index }})"
                            @class([
                                'flex flex-col items-center gap-1 p-4 rounded-2xl border-2 text-center transition-all duration-200 cursor-pointer',
                                'col-span-2'                                                              => $index === 0,
                                'border-violet-600 bg-violet-50 ring-2 ring-violet-200 shadow-md'        => $selectedTierIndex === $index,
                                'border-gray-200 bg-white hover:border-violet-400 hover:bg-gray-50'      => $selectedTierIndex !== $index,
                            ])
                        >
                            {{-- "Best Value" badge in natural flow — no absolute positioning --}}
                            @if($index === 0)
                                <span class="inline-flex items-center bg-violet-600 text-white text-xs font-bold px-3 py-0.5 rounded-full shadow-sm whitespace-nowrap">
                                    الأوفر 🔥
                                </span>
                            @endif

                            <div class="text-base font-bold text-gray-900">
                                {{ $tier['quantity'] }} {{ $tier['quantity'] == 1 ? 'قطعة' : 'قطع' }}
                            </div>
                            <div class="font-bold {{ $index === 0 ? 'text-2xl text-violet-700' : 'text-lg text-violet-600' }}">
                                @if($tier['discount_type'] === 'fixed_price')
                                    {{ number_format($tier['fixed_price'], 0) }} ج.م
                                @else
                                    خصم {{ $tier['discount_percentage'] }}%
                                @endif
                            </div>

                            @if($selectedTierIndex === $index)
                                <div class="flex justify-center">
                                    <svg class="w-5 h-5 text-violet-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════
             PRE-COMPUTED PHP VARIABLES used by Alpine and the sticky bar
        ═══════════════════════════════════════════════════════════════════ --}}
        @php
            $categoryConditionCount = collect($conditionData)->where('type', 'category')->count();
            $productConditionsForAlpine = collect($conditionData)
                ->where('type', 'product')
                ->map(fn($d, $id) => ['conditionId' => (int)$id, 'has_variants' => (bool)$d['has_variants']])
                ->values()
                ->toArray();
            $tierQuantitiesJson = json_encode(array_column($tiers, 'quantity'));
        @endphp

        {{-- ═══════════════════════════════════════════════════════════════════
             CONDITION SECTIONS
        ═══════════════════════════════════════════════════════════════════ --}}
        <div class="space-y-5">
            @foreach($conditionData as $conditionId => $data)

                {{-- ───────────────────────────────────────────────────────────
                     PRODUCT-TYPE CONDITION — fixed product, choose variant only
                ─────────────────────────────────────────────────────────────── --}}
                @if($data['type'] === 'product')
                    <div id="piece-{{ $conditionId }}"
                         class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden scroll-mt-20
                                {{ isset($errors[$conditionId]) ? 'ring-2 ring-red-300' : '' }}">

                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center justify-between gap-2">
                                <h2 class="text-base font-bold text-gray-900 leading-tight">
                                    {{ $data['product_name'] }}
                                    @if($showScrollNudge && $this->getFirstUnselectedSlot() === (string)$conditionId)
                                        <span class="inline-block animate-bounce ms-1 text-violet-600">
                                            <svg class="w-4 h-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </span>
                                    @endif
                                </h2>
                                @if($data['required_quantity'] > 1)
                                    <span class="shrink-0 text-xs font-semibold text-violet-600 bg-violet-50 px-2.5 py-1 rounded-full">
                                        الكمية: {{ $data['required_quantity'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-4">
                            {{-- Product info row --}}
                            <div class="flex items-center gap-3 mb-4">
                                <img src="{{ $data['product_image'] }}"
                                     alt="{{ $data['product_name'] }}"
                                     class="w-16 h-16 object-contain bg-gray-50 rounded-xl border border-gray-200 shrink-0">
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $data['product_name'] }}</p>
                                    @if($data['is_on_sale'] ?? false)
                                        <p class="text-xs mt-0.5">
                                            <span class="text-gray-400 line-through">{{ number_format($data['regular_price'], 0) }} ج.م</span>
                                            <span class="text-red-600 font-bold ms-1">{{ number_format($data['product_price'], 0) }} ج.م</span>
                                            <span class="bg-red-100 text-red-700 text-xs font-bold px-1.5 py-0.5 rounded-full ms-1">خصم</span>
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-500 mt-0.5">{{ number_format($data['product_price'], 0) }} ج.م</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Variant picker --}}
                            @if($data['has_variants'])
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-2">اختر النوع:</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($data['variants'] as $variant)
                                            <button
                                                type="button"
                                                wire:click="selectVariant({{ $conditionId }}, {{ $variant['id'] }})"
                                                @class([
                                                    'relative px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150',
                                                    'border-2 border-violet-600 bg-violet-50 text-violet-700' => ($selections[$conditionId]['variant_id'] ?? null) === $variant['id'],
                                                    'border-2 border-gray-300 text-gray-700 hover:border-violet-400' => ($selections[$conditionId]['variant_id'] ?? null) !== $variant['id'] && $variant['stock'] > 0,
                                                    'border-2 border-gray-200 text-gray-400 cursor-not-allowed opacity-50' => $variant['stock'] <= 0,
                                                ])
                                                {{ $variant['stock'] <= 0 ? 'disabled' : '' }}
                                            >
                                                {{ $variant['name'] }}
                                                @if($variant['stock'] <= 0)
                                                    <span class="block text-xs text-red-500">نفذ</span>
                                                @elseif($variant['stock'] <= 5)
                                                    <span class="block text-xs text-orange-500">{{ $variant['stock'] }} متبقية</span>
                                                @endif
                                                @if(($selections[$conditionId]['variant_id'] ?? null) === $variant['id'])
                                                    <svg class="w-4 h-4 text-violet-600 absolute top-1 right-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(isset($errors[$conditionId]))
                                <div class="mt-3 text-sm text-red-600 font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $errors[$conditionId] }}
                                </div>
                            @endif
                        </div>
                    </div>

                {{-- ───────────────────────────────────────────────────────────
                     CATEGORY-TYPE CONDITION — Smart Quantity Selector
                     Alpine manages all +/− state client-side (zero latency).
                     State is PRESERVED across tier changes via $watch on
                     $wire.selectedTierIndex — quantities survive upgrade/downgrade.
                ─────────────────────────────────────────────────────────────── --}}
                @elseif($data['type'] === 'category')
                    @php
                        $productsJson = json_encode($data['products'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                        $conditionHasAnyError = collect($errors)->keys()
                            ->contains(fn($k) => str_starts_with((string)$k, "{$conditionId}.") || (string)$k === (string)$conditionId);
                    @endphp

                    <div
                        id="piece-{{ $conditionId }}"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden scroll-mt-20
                               {{ $conditionHasAnyError ? 'ring-2 ring-red-300' : '' }}"
                        wire:key="cat-cond-{{ $conditionId }}"
                        x-data="{
                            conditionId:     {{ $conditionId }},
                            limit:           {{ $data['required_quantity'] }},
                            tierQuantities:  {{ $tierQuantitiesJson }},
                            products:        {{ $productsJson }},
                            quantities:      {},
                            variantSelections: {},

                            get total() {
                                return Object.values(this.quantities).reduce(function(s, v){ return s + v; }, 0);
                            },

                            get overflow() {
                                return Math.max(0, this.total - this.limit);
                            },

                            isFulfilled() {
                                if (this.total !== this.limit) return false;
                                var self = this;
                                return !Object.entries(this.quantities).some(function(entry) {
                                    var pidStr = entry[0], qty = entry[1];
                                    if (qty <= 0) return false;
                                    var product = self.products.find(function(p){ return p.id === parseInt(pidStr); });
                                    return product && product.has_variants && !self.variantSelections[parseInt(pidStr)];
                                });
                            },

                            increment(productId) {
                                if (this.total >= this.limit) return;
                                this.quantities[productId] = (this.quantities[productId] || 0) + 1;
                                this.broadcast();
                            },

                            decrement(productId) {
                                var qty = this.quantities[productId] || 0;
                                if (qty <= 0) return;
                                if (qty === 1) {
                                    delete this.quantities[productId];
                                    delete this.variantSelections[productId];
                                } else {
                                    this.quantities[productId] = qty - 1;
                                }
                                this.broadcast();
                            },

                            pickVariant(productId, variantId) {
                                this.variantSelections[productId] = variantId;
                                this.broadcast();
                            },

                            buildSlots() {
                                var slots = {};
                                var idx = 0;
                                Object.entries(this.quantities).forEach(function(entry) {
                                    var pidStr = entry[0], qty = entry[1];
                                    if (qty > 0) {
                                        var pid = parseInt(pidStr);
                                        var vid = this.variantSelections[pid] || null;
                                        for (var i = 0; i < qty; i++) {
                                            slots[idx++] = { product_id: pid, variant_id: vid };
                                        }
                                    }
                                }.bind(this));
                                return slots;
                            },

                            broadcast() {
                                this.$dispatch('combo:condition-updated', {
                                    conditionId: this.conditionId,
                                    total:       this.total,
                                    limit:       this.limit,
                                    fulfilled:   this.isFulfilled(),
                                    slots:       this.buildSlots()
                                });
                            }
                        }"
                        x-init="
                            broadcast();
                            $watch('$wire.selectedTierIndex', function(idx) {
                                limit = tierQuantities[idx];
                                broadcast();
                            });
                        "
                    >
                        {{-- Card header with live progress pill --}}
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center justify-between gap-2">
                                <h2 class="text-base font-bold text-gray-900 leading-tight">
                                    اختر من: {{ $data['category_name'] }}
                                </h2>
                                <span class="shrink-0 text-sm font-bold px-3 py-1 rounded-full transition-colors duration-200"
                                      :class="total > limit ? 'bg-red-100 text-red-700' : total === limit ? 'bg-green-100 text-green-700' : 'bg-violet-50 text-violet-700'">
                                    <span x-text="total"></span> / {{ $data['required_quantity'] }}
                                </span>
                            </div>
                            {{-- Animated progress bar --}}
                            <div class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300"
                                     :class="total > limit ? 'bg-red-500' : total === limit ? 'bg-green-500' : 'bg-violet-500'"
                                     :style="'width: ' + Math.min(Math.round(total / limit * 100), 100) + '%'">
                                </div>
                            </div>
                        </div>

                        {{-- ── Product list: each product shown ONCE with +/− stepper ── --}}
                        <div class="divide-y divide-gray-50">
                            @foreach($data['products'] as $product)
                                <div class="px-4 py-3">

                                    {{-- Product row: image · name+price · stepper --}}
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $product['image'] }}"
                                             alt="{{ $product['name'] }}"
                                             class="w-12 h-12 object-contain bg-gray-50 rounded-xl border border-gray-100 flex-shrink-0">

                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-900 text-sm leading-snug break-words">{{ $product['name'] }}</p>
                                            @if($product['is_on_sale'] ?? false)
                                                <p class="text-xs mt-0.5">
                                                    <span class="text-gray-400 line-through">{{ number_format($product['regular_price'], 0) }}</span>
                                                    <span class="text-red-600 font-bold ms-1">{{ number_format($product['price'], 0) }} ج.م</span>
                                                </p>
                                            @else
                                                <p class="text-sm text-gray-500 mt-0.5">{{ number_format($product['price'], 0) }} ج.م</p>
                                            @endif
                                        </div>

                                        {{-- +/− Quantity Stepper --}}
                                        <div class="w-24 flex-shrink-0 flex items-center justify-center gap-1.5">
                                            {{-- Minus --}}
                                            <button
                                                type="button"
                                                @click="decrement({{ $product['id'] }})"
                                                :disabled="!quantities[{{ $product['id'] }}] || quantities[{{ $product['id'] }}] <= 0"
                                                class="w-8 h-8 rounded-full border-2 flex items-center justify-center font-bold text-base leading-none transition-all duration-150 select-none"
                                                :class="(!quantities[{{ $product['id'] }}] || quantities[{{ $product['id'] }}] <= 0)
                                                    ? 'border-gray-200 text-gray-300 cursor-not-allowed'
                                                    : 'border-violet-500 text-violet-600 hover:bg-violet-50 active:scale-90'"
                                                aria-label="تقليل كمية {{ $product['name'] }}"
                                            >−</button>

                                            {{-- Count --}}
                                            <span class="w-6 text-center font-bold text-gray-900 text-sm tabular-nums"
                                                  x-text="quantities[{{ $product['id'] }}] || 0"></span>

                                            {{-- Plus --}}
                                            <button
                                                type="button"
                                                @click="increment({{ $product['id'] }})"
                                                :disabled="total >= limit"
                                                class="w-8 h-8 rounded-full border-2 flex items-center justify-center font-bold text-base leading-none transition-all duration-150 select-none"
                                                :class="total >= limit
                                                    ? 'border-gray-200 text-gray-300 cursor-not-allowed'
                                                    : 'border-violet-500 text-violet-600 hover:bg-violet-50 active:scale-90'"
                                                aria-label="زيادة كمية {{ $product['name'] }}"
                                            >+</button>
                                        </div>
                                    </div>

                                    {{-- ── Inline Variant Picker — reveals when qty > 0 ── --}}
                                    @if($product['has_variants'])
                                        <div
                                            x-show="quantities[{{ $product['id'] }}] > 0"
                                            x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 -translate-y-1"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            x-transition:leave="transition ease-in duration-100"
                                            x-transition:leave-start="opacity-100 translate-y-0"
                                            x-transition:leave-end="opacity-0 -translate-y-1"
                                            class="mt-3 pt-3 border-t border-gray-100"
                                        >
                                            <p class="text-xs font-semibold text-gray-600 mb-2">اختر النوع:</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($product['variants'] as $variant)
                                                    <button
                                                        type="button"
                                                        @click="pickVariant({{ $product['id'] }}, {{ $variant['id'] }})"
                                                        :class="variantSelections[{{ $product['id'] }}] === {{ $variant['id'] }}
                                                            ? 'border-violet-600 bg-violet-50 text-violet-700 font-bold'
                                                            : '{{ $variant['stock'] <= 0 ? 'border-gray-200 text-gray-300 cursor-not-allowed opacity-50' : 'border-gray-300 text-gray-600 hover:border-violet-400 active:bg-violet-50' }}'"
                                                        class="px-3 py-1.5 rounded-lg border-2 text-xs transition-all duration-150 relative"
                                                        {{ $variant['stock'] <= 0 ? 'disabled' : '' }}
                                                    >
                                                        {{ $variant['name'] }}
                                                        @if($variant['stock'] <= 0)
                                                            <span class="block text-red-400 text-xs">(نفذ)</span>
                                                        @elseif($variant['stock'] <= 5)
                                                            <span class="block text-orange-400 text-xs">({{ $variant['stock'] }})</span>
                                                        @endif
                                                    </button>
                                                @endforeach
                                            </div>
                                            {{-- Variant-required inline warning --}}
                                            <p x-show="quantities[{{ $product['id'] }}] > 0 && !variantSelections[{{ $product['id'] }}]"
                                               class="text-xs text-orange-600 font-medium mt-1.5 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                يرجى اختيار النوع
                                            </p>
                                        </div>
                                    @endif

                                </div>
                            @endforeach
                        </div>

                        {{-- Limit-reached success banner --}}
                        <div
                            x-show="total === limit"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="bg-green-50 border-t border-green-100 px-4 py-2.5 text-center"
                        >
                            <p class="text-sm font-semibold text-green-700 flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                ممتاز! اكتملت اختياراتك
                            </p>
                        </div>

                        {{-- Livewire-side validation error (edge case: stock issue on submit) --}}
                        @if($conditionHasAnyError)
                            <div class="bg-red-50 border-t border-red-100 px-4 py-2.5 text-sm text-red-600 font-medium flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                يرجى اختيار منتجات صحيحة من هذا القسم
                            </div>
                        @endif
                    </div>

                @endif
            @endforeach
        </div>

        {{-- Offer validity --}}
        @if($combo->ends_at)
            <div class="text-center mt-6 text-sm text-gray-500">
                العرض ساري حتى {{ $combo->ends_at->format('Y/m/d') }}
            </div>
        @endif

    </div>{{-- /max-w container --}}

    {{-- ═══════════════════════════════════════════════════════════════════════
         STICKY SUMMARY BAR
         Fixed to viewport bottom. Aggregates Alpine events from all category
         condition components via window event combo:condition-updated.
         Bridge: $wire.set('selections', merged).then(() => $wire.addAllToCart())
    ═══════════════════════════════════════════════════════════════════════ --}}
    @php
        $productConditionsJson = json_encode($productConditionsForAlpine, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    @endphp

    <div
        class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-[0_-4px_24px_rgba(109,40,217,0.08)]"
        x-data="{
            categoryConditionCount: {{ $categoryConditionCount }},
            productConditions:      {{ $productConditionsJson }},
            conditionStats:         {},
            processing:             false,

            get totalSelected() {
                return Object.values(this.conditionStats).reduce(function(s, c){ return s + (c.total || 0); }, 0);
            },

            get totalRequired() {
                return Object.values(this.conditionStats).reduce(function(s, c){ return s + (c.limit || 0); }, 0);
            },

            allCategoryFulfilled() {
                if (this.categoryConditionCount === 0) return true;
                var fulfilled = Object.values(this.conditionStats).filter(function(c){ return c.fulfilled; }).length;
                return fulfilled === this.categoryConditionCount;
            },

            allProductsFulfilled(wireSelections) {
                if (!this.productConditions.length) return true;
                return this.productConditions.every(function(pc) {
                    if (!pc.has_variants) return true;
                    var sel = wireSelections && wireSelections[pc.conditionId];
                    return sel && sel.variant_id !== null;
                });
            },

            isReady(wireSelections) {
                return this.overflowQuantity === 0 && this.allCategoryFulfilled() && this.allProductsFulfilled(wireSelections);
            },

            get overflowQuantity() {
                return Math.max(0, this.totalSelected - this.totalRequired);
            },

            onConditionUpdate(detail) {
                this.conditionStats[detail.conditionId] = detail;
            }
        }"
        x-init="
            var stickyEl = $el;
            var ro = new ResizeObserver(function(entries) {
                for (var i = 0; i < entries.length; i++) {
                    document.documentElement.style.setProperty('--sticky-bar-height', entries[i].target.offsetHeight + 'px');
                }
            });
            ro.observe(stickyEl);
        "
        @combo:condition-updated.window="onConditionUpdate($event.detail)"
    >
        <div class="max-w-4xl mx-auto px-4 py-3">

            {{-- Progress row (only when category conditions exist) --}}
            @if($categoryConditionCount > 0)
                {{-- Overflow warning (downgrade scenario) --}}
                <div x-show="overflowQuantity > 0"
                     x-transition
                     class="flex items-center gap-2 mb-2.5 px-3 py-2 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span x-text="'اختياراتك الحالية تتجاوز العرض المختار. يرجى إزالة ' + overflowQuantity + ' قطعة.'"></span>
                </div>

                {{-- Normal progress row --}}
                <div class="flex items-center justify-between mb-2.5 gap-3"
                     x-show="overflowQuantity === 0 && totalRequired > 0">
                    <div class="flex items-center gap-1.5 text-sm">
                        <span class="font-bold transition-colors duration-200"
                              :class="totalSelected === totalRequired && totalRequired > 0 ? 'text-green-600' : 'text-violet-700'"
                              x-text="totalSelected"></span>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-600 font-medium" x-text="totalRequired"></span>
                        <span class="text-gray-500">قطعة</span>
                        <span x-show="totalSelected === totalRequired && totalRequired > 0"
                              x-transition
                              class="text-green-600 font-bold">✓</span>
                    </div>
                    <div class="text-lg font-bold text-violet-700">
                        {{ number_format($comboPrice, 2) }} ج.م
                        @if($originalPrice > $comboPrice)
                            <span class="text-xs text-gray-400 line-through font-normal ms-1">{{ number_format($originalPrice, 2) }}</span>
                        @endif
                    </div>
                </div>

                {{-- Mini progress bar --}}
                <div class="h-1 bg-gray-200 rounded-full mb-3 overflow-hidden" x-show="totalRequired > 0">
                    <div class="h-full rounded-full transition-all duration-300"
                         :class="overflowQuantity > 0 ? 'bg-red-500' : totalSelected === totalRequired && totalRequired > 0 ? 'bg-green-500' : 'bg-violet-500'"
                         :style="totalRequired > 0 ? 'width: ' + Math.min(Math.round(totalSelected / totalRequired * 100), 100) + '%' : 'width:0'">
                    </div>
                </div>
            @else
                {{-- No category conditions: just show price --}}
                <div class="text-center mb-3">
                    <span class="text-xl font-bold text-violet-700">{{ number_format($comboPrice, 2) }} ج.م</span>
                    @if($originalPrice > $comboPrice)
                        <span class="text-sm text-gray-400 line-through ms-2">{{ number_format($originalPrice, 2) }} ج.م</span>
                    @endif
                </div>
            @endif

            {{-- ── CTA Buttons ── --}}
            <div class="grid grid-cols-2 gap-2">

                {{-- Add to Cart --}}
                <button
                    type="button"
                    :disabled="!isReady($wire.selections) || processing"
                    :class="isReady($wire.selections) && !processing
                        ? 'bg-violet-600 hover:bg-violet-700 text-white shadow-md active:scale-[0.98]'
                        : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                    class="flex items-center justify-center gap-1.5 px-3 py-3.5 rounded-xl font-bold text-sm transition-all duration-200"
                    @click="
                        if (!isReady($wire.selections) || processing) return;
                        processing = true;
                        var catPayload = {};
                        Object.entries(conditionStats).forEach(function(entry) {
                            catPayload[parseInt(entry[0])] = entry[1].slots;
                        });
                        var merged = Object.assign({}, $wire.selections || {}, catPayload);
                        $wire.set('selections', merged)
                            .then(function(){ return $wire.addAllToCart(); })
                            .finally(function(){ processing = false; });
                    "
                >
                    <span x-show="!processing" class="flex items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        أضف للسلة
                    </span>
                    <span x-show="processing" class="flex items-center gap-1.5">
                        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        جاري...
                    </span>
                </button>

                {{-- Buy Now --}}
                <button
                    type="button"
                    :disabled="!isReady($wire.selections) || processing"
                    :class="isReady($wire.selections) && !processing
                        ? 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-md active:scale-[0.98]'
                        : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                    class="flex items-center justify-center gap-1.5 px-3 py-3.5 rounded-xl font-bold text-sm transition-all duration-200"
                    @click="
                        if (!isReady($wire.selections) || processing) return;
                        processing = true;
                        var catPayload = {};
                        Object.entries(conditionStats).forEach(function(entry) {
                            catPayload[parseInt(entry[0])] = entry[1].slots;
                        });
                        var merged = Object.assign({}, $wire.selections || {}, catPayload);
                        $wire.set('selections', merged)
                            .then(function(){ return $wire.buyNow(); })
                            .finally(function(){ processing = false; });
                    "
                >
                    <span x-show="!processing" class="flex items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        اشتري الآن
                    </span>
                    <span x-show="processing" class="flex items-center gap-1.5">
                        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        جاري...
                    </span>
                </button>

            </div>
        </div>
    </div>

    {{-- WhatsApp Floating Button (positioned above sticky bar via CSS var) --}}
    <a href="https://wa.me/{{ env('WHATSAPP_NUMBER', '201091191056') }}?text={{ urlencode('مرحباً، أريد الاستفسار عن منتج: ' . $combo->name) }}"
       target="_blank"
       rel="noopener noreferrer"
       class="fixed z-30"
       style="bottom: calc(var(--sticky-bar-height, 0px) + 16px); {{ app()->getLocale() === 'ar' ? 'right: 24px;' : 'left: 24px;' }} display: flex; align-items: center; justify-content: center; width: 56px; height: 56px; border-radius: 9999px; background-color: #25D366; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); transition: transform 0.3s;"
       aria-label="Contact on WhatsApp"
    >
        <svg xmlns="http://www.w3.org/2000/svg" style="width:32px;height:32px;fill:white;display:block;" viewBox="0 0 448 512">
            <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
        </svg>
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
                @if($combo->custom_pixel_id)
                {{-- Initialize the custom per-offer pixel. Per FB standard behavior,
                     subsequent fbq('track') calls will fire to BOTH this pixel
                     and the global store pixel already initialized in the layout. --}}
                fbq('init', '{{ $combo->custom_pixel_id }}');
                @endif
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

