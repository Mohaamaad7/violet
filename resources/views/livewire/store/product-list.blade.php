{{-- Single Root Element for Livewire v3 --}}
<div x-data="{ 
    openCategories: true, 
    openBrands: false,
    openPrice: false, 
    openRating: false,
    openStock: false,
    openSale: false,
    showFilters: false 
}">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Dark Overlay for Mobile Off-Canvas --}}
        <div x-show="showFilters" @click="showFilters = false" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 z-40 lg:hidden" style="display: none;">
        </div>

        {{-- Desktop Sidebar --}}
        <aside class="hidden lg:block lg:col-span-3 flex-shrink-0 self-start"
            style="position: sticky; top: 170px; z-index: 40; isolation: isolate;">
            <div
                class="bg-white border border-gray-300 rounded-lg shadow-sm overflow-hidden max-h-[calc(100vh-190px)] flex flex-col">
                {{-- Filters Header --}}
                <div class="px-6 py-4 border-b border-gray-300 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">
                            <i class="fas fa-filter text-violet-600 ltr:mr-2 rtl:ml-2"></i>
                            {{ __('messages.filters') }}
                        </h2>
                        @if($this->hasActiveFilters)
                            <button wire:click="clearFilters"
                                class="text-sm text-violet-600 hover:text-violet-800 font-semibold transition-all duration-200 hover:underline">
                                {{ __('messages.clear_all') }}
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Filters Content Container --}}
                <div class="px-6 py-4 overflow-y-auto flex-1">
                    {{-- Search Filter --}}
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2 mb-4">
                            <i class="fas fa-search text-violet-600 text-sm"></i>
                            {{ __('messages.search') }}
                        </h3>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.400ms="search"
                                class="w-full px-4 py-2.5 ltr:pr-10 rtl:pl-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all duration-200"
                                placeholder="{{ __('messages.search_products') }}">
                            @if($search)
                                <button wire:click="clearSearch"
                                    class="absolute ltr:right-3 rtl:left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            @else
                                <i
                                    class="fas fa-search absolute ltr:right-3 rtl:left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            @endif
                        </div>
                    </div>

                    {{-- Categories Filter - Nested Accordion --}}
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <button @click="openCategories = !openCategories"
                            class="flex items-center justify-between w-full mb-4 group">
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-tag text-violet-600 text-sm"></i>
                                {{ __('messages.categories') }}
                            </h3>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                                :class="openCategories ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="openCategories" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0" class="space-y-1 pr-1">
                            @foreach($categories as $parentCategory)
                                <div x-data="{ expanded: false }" class="mb-1">
                                    {{-- Parent Category --}}
                                    <div
                                        class="flex items-center gap-2 py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                        {{-- Expand/Collapse Button (only if has children) --}}
                                        @if($parentCategory->children->count() > 0)
                                            <button @click="expanded = !expanded"
                                                class="p-1 hover:bg-violet-100 rounded transition-colors duration-200"
                                                type="button">
                                                <i class="fas fa-chevron-right text-xs text-gray-500 transition-transform duration-200 rtl:rotate-180"
                                                    :class="expanded ? 'ltr:rotate-90 rtl:rotate-90' : ''"></i>
                                            </button>
                                        @else
                                            <span class="w-6"></span>
                                        @endif

                                        {{-- Parent Checkbox --}}
                                        <div class="flex items-center gap-3 group flex-1">
                                            <div class="relative">
                                                <input type="checkbox"
                                                    wire:click="toggleCategory({{ $parentCategory->id }})"
                                                    :checked="$wire.selectedCategories.includes({{ $parentCategory->id }})"
                                                    class="peer w-4 h-4 text-violet-600 border-2 border-gray-400 rounded focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer checked:border-violet-600">
                                            </div>
                                            <span
                                                class="text-sm text-gray-800 group-hover:text-violet-700 font-semibold transition-colors duration-200 flex-1">
                                                {{ $parentCategory->name }}
                                            </span>
                                            <span
                                                class="text-xs text-gray-500 group-hover:text-violet-600 transition-colors duration-200">
                                                ({{ $parentCategory->getTotalActiveProductsCount() }})
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Children Categories (Nested) --}}
                                    @if($parentCategory->children->count() > 0)
                                        <div x-show="expanded" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 -translate-y-1"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            class="ltr:ml-8 rtl:mr-8 mt-1 space-y-1" style="display: none;">
                                            @foreach($parentCategory->children as $childCategory)
                                                <div
                                                    class="flex items-center gap-3 group py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                                    <div class="relative">
                                                        <input type="checkbox" wire:click="toggleCategory({{ $childCategory->id }})"
                                                            :checked="$wire.selectedCategories.includes({{ $childCategory->id }})"
                                                            class="peer w-4 h-4 text-violet-600 border-2 border-gray-400 rounded focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer checked:border-violet-600">
                                                    </div>
                                                    <span
                                                        class="text-sm text-gray-700 group-hover:text-violet-700 font-medium transition-colors duration-200 flex-1">
                                                        {{ $childCategory->name }}
                                                    </span>
                                                    <span
                                                        class="text-xs text-gray-500 group-hover:text-violet-600 transition-colors duration-200">
                                                        ({{ $childCategory->products()->where('status', 'active')->count() }})
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Brand Filter - Collapsible --}}
                    @if(count($this->availableBrands) > 0)
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <button @click="openBrands = !openBrands"
                                class="flex items-center justify-between w-full mb-4 group">
                                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-building text-violet-600 text-sm"></i>
                                    {{ __('messages.brand') }}
                                </h3>
                                <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                                    :class="openBrands ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="openBrands" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach($this->availableBrands as $brand)
                                    <label
                                        class="flex items-center gap-3 cursor-pointer group py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                        <input type="checkbox" wire:click="toggleBrand('{{ $brand }}')"
                                            :checked="$wire.selectedBrands.includes('{{ $brand }}')"
                                            class="w-4 h-4 text-violet-600 border-2 border-gray-400 rounded focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer">
                                        <span
                                            class="text-sm text-gray-700 group-hover:text-violet-700 font-medium transition-colors duration-200">
                                            {{ $brand }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Price Range Filter - Collapsible --}}
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <button @click="openPrice = !openPrice"
                            class="flex items-center justify-between w-full mb-4 group">
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-dollar-sign text-violet-600 text-sm"></i>
                                {{ __('messages.price') }}
                            </h3>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                                :class="openPrice ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="openPrice" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0" class="space-y-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <input type="number" wire:model.live.debounce.500ms="minPrice" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-medium focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all duration-200"
                                        placeholder="{{ __('messages.min') }}">
                                </div>
                                <span class="text-gray-400 font-bold">—</span>
                                <div class="flex-1">
                                    <input type="number" wire:model.live.debounce.500ms="maxPrice" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-medium focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all duration-200"
                                        placeholder="{{ __('messages.max') }}">
                                </div>
                            </div>

                            @if($minPrice !== '' || $maxPrice !== '')
                                <div
                                    class="bg-violet-50 border border-violet-200 rounded-lg p-3 flex items-center justify-center gap-2">
                                    <span class="text-sm font-bold text-violet-800">
                                        {{ number_format($minPrice !== '' ? $minPrice : $this->priceRange['min'], 0) }} ج.م
                                        - {{ number_format($maxPrice !== '' ? $maxPrice : $this->priceRange['max'], 0) }}
                                        ج.م
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- On Sale Filter --}}
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <button @click="openSale = !openSale"
                            class="flex items-center justify-between w-full mb-4 group">
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-percent text-violet-600 text-sm"></i>
                                {{ __('messages.on_sale') }}
                            </h3>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                                :class="openSale ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="openSale" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0">
                            <label
                                class="flex items-center gap-3 cursor-pointer group py-2 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="onSaleOnly" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600">
                                    </div>
                                </div>
                                <span
                                    class="text-sm text-gray-700 group-hover:text-violet-700 font-medium transition-colors duration-200">
                                    {{ __('messages.show_sale_items_only') }}
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Stock Status Filter --}}
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <button @click="openStock = !openStock"
                            class="flex items-center justify-between w-full mb-4 group">
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-warehouse text-violet-600 text-sm"></i>
                                {{ __('messages.availability') }}
                            </h3>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                                :class="openStock ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="openStock" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0" class="space-y-2">
                            <label
                                class="flex items-center gap-3 cursor-pointer group py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                <input type="radio" wire:model.live="stockStatus" value="all"
                                    class="w-4 h-4 text-violet-600 border-2 border-gray-400 focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer">
                                <span
                                    class="text-sm text-gray-700 group-hover:text-violet-700 font-medium transition-colors duration-200">
                                    {{ __('messages.all_products') }}
                                </span>
                            </label>
                            <label
                                class="flex items-center gap-3 cursor-pointer group py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                <input type="radio" wire:model.live="stockStatus" value="in_stock"
                                    class="w-4 h-4 text-violet-600 border-2 border-gray-400 focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer">
                                <span
                                    class="text-sm text-gray-700 group-hover:text-violet-700 font-medium transition-colors duration-200 flex items-center gap-2">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    {{ __('messages.in_stock') }}
                                </span>
                            </label>
                            <label
                                class="flex items-center gap-3 cursor-pointer group py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                <input type="radio" wire:model.live="stockStatus" value="out_of_stock"
                                    class="w-4 h-4 text-violet-600 border-2 border-gray-400 focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer">
                                <span
                                    class="text-sm text-gray-700 group-hover:text-violet-700 font-medium transition-colors duration-200 flex items-center gap-2">
                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                    {{ __('messages.out_of_stock') }}
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Rating Filter - Collapsible --}}
                    <div class="mb-2">
                        <button @click="openRating = !openRating"
                            class="flex items-center justify-between w-full mb-4 group">
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-star text-violet-600 text-sm"></i>
                                {{ __('messages.customer_reviews') }}
                            </h3>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                                :class="openRating ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="openRating" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0" class="space-y-2">
                            @foreach([5, 4, 3, 2, 1] as $rating)
                                <label
                                    class="flex items-center gap-2 cursor-pointer group py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                    <input type="radio" wire:model.live="selectedRating" value="{{ $rating }}"
                                        class="w-4 h-4 text-violet-600 border-2 border-gray-400 focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer checked:border-violet-600">
                                    <div class="flex items-center gap-1.5 flex-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-sm {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }} 
                                                       group-hover:scale-110 transition-transform duration-200"></i>
                                        @endfor
                                        <span
                                            class="text-sm font-medium text-gray-800 group-hover:text-violet-700 ltr:ml-1 rtl:mr-1 transition-colors duration-200">
                                            {{ __('messages.and_up') }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach

                            @if($selectedRating)
                                <button wire:click="clearRating"
                                    class="text-xs text-violet-600 hover:text-violet-800 font-semibold mt-2 flex items-center gap-1 hover:underline transition-all duration-200">
                                    <i class="fas fa-times text-xs"></i>
                                    {{ __('messages.clear_rating') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Mobile Off-Canvas Filter Panel (Bottom Sheet on mobile) --}}
        <div x-show="showFilters" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full sm:-translate-x-full sm:translate-y-0"
            x-transition:enter-end="translate-y-0 sm:translate-x-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0 sm:translate-x-0"
            x-transition:leave-end="translate-y-full sm:-translate-x-full sm:translate-y-0"
            class="fixed inset-x-0 bottom-0 sm:inset-y-0 sm:inset-x-auto sm:left-0 sm:w-80 bg-white shadow-2xl z-50 lg:hidden overflow-hidden rounded-t-3xl sm:rounded-none max-h-[85vh] sm:max-h-full flex flex-col"
            style="display: none;">
            {{-- Mobile Filters Header with Close Button --}}
            <div class="px-6 py-4 border-b border-gray-300 bg-gray-50 sticky top-0 z-10 flex-shrink-0">
                {{-- Drag Handle for Mobile --}}
                <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3 sm:hidden"></div>

                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-filter text-violet-600 ltr:mr-2 rtl:ml-2"></i>
                        {{ __('messages.filters') }}
                        @if($this->activeFiltersCount > 0)
                            <span class="ml-2 px-2 py-0.5 bg-violet-100 text-violet-700 rounded-full text-sm">
                                {{ $this->activeFiltersCount }}
                            </span>
                        @endif
                    </h2>
                    <div class="flex items-center gap-3">
                        @if($this->hasActiveFilters)
                            <button wire:click="clearFilters"
                                class="text-sm text-violet-600 hover:text-violet-800 font-semibold transition-all duration-200 hover:underline">
                                {{ __('messages.clear_all') }}
                            </button>
                        @endif
                        <button @click="showFilters = false"
                            class="p-2 hover:bg-gray-200 rounded-lg transition-colors duration-200"
                            aria-label="Close filters">
                            <i class="fas fa-times text-xl text-gray-700"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile Filters Content --}}
            <div class="px-6 py-4 overflow-y-auto flex-1">
                {{-- Mobile Search --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="fas fa-search text-violet-600 text-sm"></i>
                        {{ __('messages.search') }}
                    </h3>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.400ms="search"
                            class="w-full px-4 py-2.5 ltr:pr-10 rtl:pl-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all duration-200"
                            placeholder="{{ __('messages.search_products') }}">
                        <i
                            class="fas fa-search absolute ltr:right-3 rtl:left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                {{-- Mobile Categories Filter --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <button @click="openCategories = !openCategories"
                        class="flex items-center justify-between w-full mb-4 group">
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-tag text-violet-600 text-sm"></i>
                            {{ __('messages.categories') }}
                        </h3>
                        <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                            :class="openCategories ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="openCategories" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0" class="space-y-1 max-h-48 overflow-y-auto">
                        @foreach($categories as $parentCategory)
                            <div x-data="{ expanded: false }" class="mb-1">
                                <div
                                    class="flex items-center gap-2 py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                    @if($parentCategory->children->count() > 0)
                                        <button @click="expanded = !expanded"
                                            class="p-1 hover:bg-violet-100 rounded transition-colors duration-200"
                                            type="button">
                                            <i class="fas fa-chevron-right text-xs text-gray-500 transition-transform duration-200"
                                                :class="expanded ? 'rotate-90' : ''"></i>
                                        </button>
                                    @else
                                        <span class="w-6"></span>
                                    @endif

                                    <div class="flex items-center gap-3 group flex-1">
                                        <input type="checkbox" wire:click="toggleCategory({{ $parentCategory->id }})"
                                            :checked="$wire.selectedCategories.includes({{ $parentCategory->id }})"
                                            class="w-4 h-4 text-violet-600 border-2 border-gray-400 rounded focus:ring-2 focus:ring-violet-500">
                                        <span class="text-sm text-gray-800 font-semibold flex-1">
                                            {{ $parentCategory->name }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            ({{ $parentCategory->getTotalActiveProductsCount() }})
                                        </span>
                                    </div>
                                </div>

                                @if($parentCategory->children->count() > 0)
                                    <div x-show="expanded" class="ltr:ml-8 rtl:mr-8 mt-1 space-y-1" style="display: none;">
                                        @foreach($parentCategory->children as $childCategory)
                                            <div class="flex items-center gap-3 py-1.5 px-2 rounded hover:bg-violet-50">
                                                <input type="checkbox" wire:click="toggleCategory({{ $childCategory->id }})"
                                                    :checked="$wire.selectedCategories.includes({{ $childCategory->id }})"
                                                    class="w-4 h-4 text-violet-600 border-2 border-gray-400 rounded">
                                                <span class="text-sm text-gray-700 font-medium flex-1">
                                                    {{ $childCategory->name }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    ({{ $childCategory->products()->where('status', 'active')->count() }})
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Mobile Brand Filter --}}
                @if(count($this->availableBrands) > 0)
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <button @click="openBrands = !openBrands"
                            class="flex items-center justify-between w-full mb-4 group">
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-building text-violet-600 text-sm"></i>
                                {{ __('messages.brand') }}
                            </h3>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                                :class="openBrands ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="openBrands" class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($this->availableBrands as $brand)
                                <label class="flex items-center gap-3 cursor-pointer py-1.5 px-2 rounded hover:bg-violet-50">
                                    <input type="checkbox" wire:click="toggleBrand('{{ $brand }}')"
                                        :checked="$wire.selectedBrands.includes('{{ $brand }}')"
                                        class="w-4 h-4 text-violet-600 border-2 border-gray-400 rounded">
                                    <span class="text-sm text-gray-700 font-medium">{{ $brand }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Mobile Price Filter --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <button @click="openPrice = !openPrice" class="flex items-center justify-between w-full mb-4 group">
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-dollar-sign text-violet-600 text-sm"></i>
                            {{ __('messages.price') }}
                        </h3>
                        <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                            :class="openPrice ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="openPrice" class="space-y-3">
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.live.debounce.500ms="minPrice" min="0"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm"
                                placeholder="{{ __('messages.min') }}">
                            <span class="text-gray-400 font-bold">—</span>
                            <input type="number" wire:model.live.debounce.500ms="maxPrice" min="0"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm"
                                placeholder="{{ __('messages.max') }}">
                        </div>
                    </div>
                </div>

                {{-- Mobile On Sale Toggle --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <label class="flex items-center justify-between cursor-pointer py-2">
                        <span class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-percent text-violet-600 text-sm"></i>
                            {{ __('messages.on_sale') }}
                        </span>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="onSaleOnly" class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-300 peer-focus:ring-4 peer-focus:ring-violet-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600">
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Mobile Stock Status --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <button @click="openStock = !openStock" class="flex items-center justify-between w-full mb-4 group">
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-warehouse text-violet-600 text-sm"></i>
                            {{ __('messages.availability') }}
                        </h3>
                        <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                            :class="openStock ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="openStock" class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer py-1.5 px-2 rounded hover:bg-violet-50">
                            <input type="radio" wire:model.live="stockStatus" value="all"
                                class="w-4 h-4 text-violet-600">
                            <span class="text-sm text-gray-700 font-medium">{{ __('messages.all_products') }}</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer py-1.5 px-2 rounded hover:bg-violet-50">
                            <input type="radio" wire:model.live="stockStatus" value="in_stock"
                                class="w-4 h-4 text-violet-600">
                            <span class="text-sm text-gray-700 font-medium flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                {{ __('messages.in_stock') }}
                            </span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer py-1.5 px-2 rounded hover:bg-violet-50">
                            <input type="radio" wire:model.live="stockStatus" value="out_of_stock"
                                class="w-4 h-4 text-violet-600">
                            <span class="text-sm text-gray-700 font-medium flex items-center gap-2">
                                <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                {{ __('messages.out_of_stock') }}
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Mobile Rating Filter --}}
                <div class="mb-2">
                    <button @click="openRating = !openRating"
                        class="flex items-center justify-between w-full mb-4 group">
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-star text-violet-600 text-sm"></i>
                            {{ __('messages.customer_reviews') }}
                        </h3>
                        <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                            :class="openRating ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="openRating" class="space-y-2">
                        @foreach([5, 4, 3, 2, 1] as $rating)
                            <label class="flex items-center gap-2 cursor-pointer py-1.5 px-2 rounded hover:bg-violet-50">
                                <input type="radio" wire:model.live="selectedRating" value="{{ $rating }}"
                                    class="w-4 h-4 text-violet-600">
                                <div class="flex items-center gap-1.5 flex-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i
                                            class="fas fa-star text-sm {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                    @endfor
                                    <span
                                        class="text-sm font-medium text-gray-800 ltr:ml-1 rtl:mr-1">{{ __('messages.and_up') }}</span>
                                </div>
                            </label>
                        @endforeach

                        @if($selectedRating)
                            <button wire:click="clearRating"
                                class="text-xs text-violet-600 hover:text-violet-800 font-semibold mt-2 flex items-center gap-1">
                                <i class="fas fa-times text-xs"></i>
                                {{ __('messages.clear_rating') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Apply Button (Mobile Only) --}}
            <div class="sticky bottom-0 bg-white border-t border-gray-300 px-6 py-4 flex-shrink-0">
                <button @click="showFilters = false"
                    class="w-full px-6 py-3 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-check ltr:mr-2 rtl:ml-2"></i>
                    {{ __('messages.apply_filters') }}
                    @if($products->total() > 0)
                        <span class="ltr:ml-2 rtl:mr-2">({{ $products->total() }} {{ __('messages.results') }})</span>
                    @endif
                </button>
            </div>
        </div>

        {{-- Products Grid Area --}}
        <div class="lg:col-span-9 relative" style="z-index: 1;">
            {{-- Active Filters Display --}}
            @if($this->hasActiveFilters)
                <div class="bg-violet-50 border border-violet-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-filter text-violet-600"></i>
                            {{ __('messages.active_filters') }}
                            <span class="px-2 py-0.5 bg-violet-200 text-violet-800 rounded-full text-xs">
                                {{ $this->activeFiltersCount }}
                            </span>
                        </h4>
                        <button wire:click="clearFilters"
                            class="text-xs text-violet-600 hover:text-violet-800 font-semibold hover:underline transition-all duration-200">
                            <i class="fas fa-times-circle"></i> {{ __('messages.clear_all') }}
                        </button>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        {{-- Search Filter Chip --}}
                        @if($search)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-violet-300 rounded-full text-sm font-medium text-gray-800 shadow-sm">
                                <i class="fas fa-search text-violet-600 text-xs"></i>
                                "{{ Str::limit($search, 15) }}"
                                <button wire:click="clearSearch" class="ltr:ml-1 rtl:mr-1 text-gray-500 hover:text-red-600">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        @endif

                        {{-- Category Filters --}}
                        @foreach($selectedCategories as $categoryId)
                            @php
                                $category = \App\Models\Category::find($categoryId);
                            @endphp
                            @if($category)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-violet-300 rounded-full text-sm font-medium text-gray-800 shadow-sm">
                                    <i class="fas fa-tag text-violet-600 text-xs"></i>
                                    {{ $category->name }}
                                    <button wire:click="removeCategory({{ $categoryId }})"
                                        class="ltr:ml-1 rtl:mr-1 text-gray-500 hover:text-red-600">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                        @endforeach

                        {{-- Brand Filters --}}
                        @foreach($selectedBrands as $brand)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-violet-300 rounded-full text-sm font-medium text-gray-800 shadow-sm">
                                <i class="fas fa-building text-violet-600 text-xs"></i>
                                {{ $brand }}
                                <button wire:click="removeBrand('{{ $brand }}')"
                                    class="ltr:ml-1 rtl:mr-1 text-gray-500 hover:text-red-600">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        @endforeach

                        {{-- Price Filter --}}
                        @if($minPrice !== '' || $maxPrice !== '')
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-violet-300 rounded-full text-sm font-medium text-gray-800 shadow-sm">
                                <i class="fas fa-dollar-sign text-violet-600 text-xs"></i>
                                {{ number_format($minPrice !== '' ? $minPrice : 0, 0) }} ج.م -
                                {{ number_format($maxPrice !== '' ? $maxPrice : 10000, 0) }} ج.م
                                <button wire:click="clearPriceFilter"
                                    class="ltr:ml-1 rtl:mr-1 text-gray-500 hover:text-red-600">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        @endif

                        {{-- On Sale Filter --}}
                        @if($onSaleOnly)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-violet-300 rounded-full text-sm font-medium text-gray-800 shadow-sm">
                                <i class="fas fa-percent text-violet-600 text-xs"></i>
                                {{ __('messages.on_sale') }}
                                <button wire:click="$set('onSaleOnly', false)"
                                    class="ltr:ml-1 rtl:mr-1 text-gray-500 hover:text-red-600">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        @endif

                        {{-- Stock Status Filter --}}
                        @if($stockStatus !== 'all')
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-violet-300 rounded-full text-sm font-medium text-gray-800 shadow-sm">
                                <i class="fas fa-warehouse text-violet-600 text-xs"></i>
                                {{ $stockStatus === 'in_stock' ? __('messages.in_stock') : __('messages.out_of_stock') }}
                                <button wire:click="$set('stockStatus', 'all')"
                                    class="ltr:ml-1 rtl:mr-1 text-gray-500 hover:text-red-600">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        @endif

                        {{-- Rating Filter --}}
                        @if($selectedRating)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-violet-300 rounded-full text-sm font-medium text-gray-800 shadow-sm">
                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                {{ $selectedRating }}+ {{ __('messages.stars') }}
                                <button wire:click="clearRating" class="ltr:ml-1 rtl:mr-1 text-gray-500 hover:text-red-600">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Toolbar: Results Count & Sort --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    {{-- Mobile Filter Button & Results Count --}}
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        {{-- Mobile Filter Button --}}
                        <button @click="showFilters = true"
                            class="lg:hidden flex items-center gap-2 px-4 py-2 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700 transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fas fa-filter"></i>
                            <span>{{ __('messages.filters') }}</span>
                            @if($this->activeFiltersCount > 0)
                                <span
                                    class="ltr:ml-1 rtl:mr-1 px-2 py-0.5 bg-white text-violet-600 rounded-full text-xs font-bold">
                                    {{ $this->activeFiltersCount }}
                                </span>
                            @endif
                        </button>

                        {{-- Results Count with Icon --}}
                        <div class="text-sm text-gray-700 flex items-center gap-2">
                            <i class="fas fa-box-open text-violet-600"></i>
                            <span class="hidden sm:inline">
                                {{ __('messages.showing') }} <span
                                    class="font-bold text-gray-900">{{ $products->firstItem() ?? 0 }}</span>
                                {{ __('messages.to') }} <span
                                    class="font-bold text-gray-900">{{ $products->lastItem() ?? 0 }}</span>
                                {{ __('messages.of') }} <span
                                    class="font-bold text-violet-700">{{ $products->total() }}</span>
                                {{ __('messages.products') }}
                            </span>
                            <span class="sm:hidden font-bold text-violet-700">
                                {{ $products->total() }} {{ __('messages.products') }}
                            </span>
                        </div>
                    </div>

                    {{-- Enhanced Sort Dropdown --}}
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label class="text-sm text-gray-700 font-semibold whitespace-nowrap flex items-center gap-1.5">
                            <i class="fas fa-sort text-violet-600"></i>
                            {{ __('messages.sort_by') }}:
                        </label>
                        <select wire:model.live="sortBy"
                            class="flex-1 sm:flex-none px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-medium focus:ring-2 focus:ring-violet-500 focus:border-violet-500 bg-white hover:border-violet-400 transition-all duration-200 cursor-pointer">
                            <option value="default">{{ __('messages.sort_featured') }}</option>
                            <option value="newest">{{ __('messages.sort_newest') }}</option>
                            <option value="price_asc">{{ __('messages.sort_price_low') }}</option>
                            <option value="price_desc">{{ __('messages.sort_price_high') }}</option>
                            <option value="rating_desc">{{ __('messages.sort_rating') }}</option>
                            <option value="popular">{{ __('messages.sort_popular') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Products Grid --}}
            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    @foreach($products as $product)
                        <div wire:key="product-{{ $product->id }}">
                            <x-store.product-card :product="$product" />
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                {{-- Enhanced Empty State --}}
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm text-center py-16 px-8">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 mx-auto mb-6 bg-violet-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-box-open text-5xl text-violet-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ __('messages.no_products_found') }}</h3>
                        <p class="text-gray-600 mb-8">
                            {{ __('messages.no_products_message') }}
                        </p>
                        <button wire:click="clearFilters"
                            class="inline-flex items-center gap-2 px-8 py-3.5 bg-violet-600 text-white rounded-lg hover:bg-violet-700 font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-redo"></i>
                            {{ __('messages.clear_all_filters') }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>