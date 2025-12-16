<div class="relative w-full" x-data="{ 
         open: @entangle('showResults'),
         selectedIndex: @entangle('selectedIndex')
     }" @click.away="$wire.closeResults()" @keydown.escape.window="$wire.closeResults()"
    @keydown.arrow-down.prevent="$wire.incrementIndex()" @keydown.arrow-up.prevent="$wire.decrementIndex()"
    @keydown.enter.prevent="$wire.selectCurrent()">

    {{-- Search Input --}}
    <div class="relative">
        <input type="text" wire:model.live.debounce.300ms="search"
            placeholder="{{ __('store.header.search_placeholder') }}"
            class="w-full px-4 py-2.5 {{ $isMobile ? 'pl-10 pr-4' : 'ltr:pr-10 rtl:pl-10 ltr:pl-4 rtl:pr-4' }} border-2 border-gray-200 rounded-lg focus:border-violet-500 focus:ring-2 focus:ring-violet-200 focus:outline-none transition"
            autocomplete="off">

        {{-- Search Icon --}}
        <svg class="w-5 h-5 absolute {{ app()->getLocale() === 'ar' ? 'left-3' : 'right-3' }} top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>

        {{-- Clear Button --}}
        @if($search)
            <button type="button" wire:click="clearSearch"
                class="absolute {{ app()->getLocale() === 'ar' ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif

        {{-- Loading Indicator --}}
        <div wire:loading wire:target="search"
            class="absolute {{ app()->getLocale() === 'ar' ? 'right-10' : 'left-10' }} top-1/2 -translate-y-1/2">
            <svg class="animate-spin h-5 w-5 text-violet-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>
    </div>

    {{-- Search Results Dropdown --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50 max-h-[500px] overflow-y-auto"
        style="display: none;">

        @if(count($results) > 0)
            {{-- Results List --}}
            <div class="divide-y divide-gray-100">
                @foreach($results as $index => $product)
                    <a href="{{ route('product.show', $product['slug']) }}" wire:key="result-{{ $product['id'] }}"
                        class="flex items-center gap-4 p-4 hover:bg-violet-50 transition-colors duration-150 {{ $selectedIndex === $index ? 'bg-violet-50' : '' }}"
                        @mouseenter="selectedIndex = {{ $index }}">

                        {{-- Product Image --}}
                        <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover">
                        </div>

                        {{-- Product Info --}}
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-semibold text-gray-900 truncate mb-1">
                                {{ $product['name'] }}
                            </h4>

                            @if($product['category'])
                                <p class="text-xs text-gray-500 mb-1">
                                    {{ $product['category'] }}
                                </p>
                            @endif

                            <div class="flex items-center gap-2">
                                {{-- Price --}}
                                <span class="text-sm font-bold text-violet-600">
                                    {{ number_format($product['price'], 2) }} ج.م
                                </span>

                                @if($product['is_on_sale'])
                                    <span class="text-xs text-gray-400 line-through">
                                        {{ number_format($product['original_price'], 2) }} ج.م
                                    </span>
                                    <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold">
                                        {{ __('store.products.sale') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Rating --}}
                            @if($product['rating'] > 0)
                                <div class="flex items-center gap-1 mt-1">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3 h-3 {{ $i <= $product['rating'] ? 'text-yellow-400' : 'text-gray-300' }}"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-xs text-gray-500">({{ number_format($product['rating'], 1) }})</span>
                                </div>
                            @endif
                        </div>

                        {{-- Stock Status --}}
                        <div class="flex-shrink-0">
                            @if($product['in_stock'])
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium">
                                    {{ __('store.products.in_stock') }}
                                </span>
                            @else
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-medium">
                                    {{ __('store.products.out_of_stock') }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- View All Results Button --}}
            <div class="p-4 bg-gray-50 border-t border-gray-200">
                <button type="button" wire:click="viewAllResults"
                    class="w-full py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-semibold rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    {{ __('store.search.view_all_results') }} ({{ count($results) }}+)
                </button>
            </div>

        @elseif($search && strlen($search) >= 2)
            {{-- No Results --}}
            <div class="p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    {{ __('store.search.no_results') }}
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    {{ __('store.search.try_different_keywords') }}
                </p>
                <button type="button" wire:click="clearSearch"
                    class="text-violet-600 hover:text-violet-700 font-medium text-sm">
                    {{ __('store.search.clear_search') }}
                </button>
            </div>
        @endif
    </div>

    {{-- Keyboard Hints (Desktop Only) --}}
    @if(!$isMobile && $showResults && count($results) > 0)
        <div
            class="absolute top-full mt-1 {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} text-xs text-gray-400 hidden lg:block">
            <span class="bg-white px-2 py-1 rounded shadow-sm border border-gray-200">
                ↑↓ {{ __('store.search.navigate') }} • Enter {{ __('store.search.select') }} • Esc
                {{ __('store.search.close') }}
            </span>
        </div>
    @endif
</div>