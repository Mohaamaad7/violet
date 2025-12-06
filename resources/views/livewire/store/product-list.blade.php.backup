{{-- Single Root Element for Livewire v3 --}}
<div x-data="{ 
    openCategories: true, 
    openPrice: false, 
    openRating: false,
    showFilters: false 
}">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Dark Overlay for Mobile Off-Canvas --}}
        <div 
            x-show="showFilters" 
            @click="showFilters = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 z-40 lg:hidden"
            style="display: none;"
        ></div>

        {{-- Desktop Sidebar --}}
        <aside class="hidden lg:block lg:col-span-3 flex-shrink-0 self-start" style="position: sticky; top: 170px; z-index: 40; isolation: isolate;">
            <div class="bg-white border border-gray-300 rounded-lg shadow-sm overflow-hidden max-h-[calc(100vh-190px)] flex flex-col">
                {{-- Filters Header --}}
                <div class="px-6 py-4 border-b border-gray-300 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">
                            <i class="fas fa-filter text-violet-600 mr-2"></i>
                            Filters
                        </h2>
                        @if(!empty($selectedCategories) || $minPrice > 0 || $maxPrice < 10000 || $selectedRating)
                        <button 
                            wire:click="clearFilters" 
                            class="text-sm text-violet-600 hover:text-violet-800 font-semibold transition-all duration-200 hover:underline"
                        >
                            Clear All
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Filters Content Container --}}
                <div class="px-6 py-4 overflow-y-auto flex-1">
                    {{-- Categories Filter - Nested Accordion --}}
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <button 
                            @click="openCategories = !openCategories"
                            class="flex items-center justify-between w-full mb-4 group"
                        >
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-tag text-violet-600 text-sm"></i>
                                Categories
                            </h3>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                               :class="openCategories ? 'rotate-180' : ''"
                            ></i>
                        </button>
                        
                        <div x-show="openCategories" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-1 pr-1"
                        >
                            @foreach(\App\Models\Category::with('children')->whereNull('parent_id')->orderBy('order')->get() as $parentCategory)
                            <div x-data="{ expanded: false }" class="mb-1">
                                {{-- Parent Category --}}
                                <div class="flex items-center gap-2 py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                    {{-- Expand/Collapse Button (only if has children) --}}
                                    @if($parentCategory->children->count() > 0)
                                    <button 
                                        @click="expanded = !expanded"
                                        class="p-1 hover:bg-violet-100 rounded transition-colors duration-200"
                                        type="button"
                                    >
                                        <i class="fas fa-chevron-right text-xs text-gray-500 transition-transform duration-200"
                                           :class="expanded ? 'rotate-90' : ''"></i>
                                    </button>
                                    @else
                                    <span class="w-6"></span>
                                    @endif

                                    {{-- Parent Checkbox --}}
                                    <div class="flex items-center gap-3 group flex-1">
                                        <div class="relative">
                                            <input 
                                                type="checkbox" 
                                                wire:model.live="selectedCategories"
                                                value="{{ $parentCategory->id }}"
                                                class="peer w-4 h-4 text-violet-600 border-2 border-gray-400 rounded focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer checked:border-violet-600"
                                            >
                                            <i class="fas fa-check absolute left-0.5 top-0.5 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity duration-200 pointer-events-none"></i>
                                        </div>
                                        <span class="text-sm text-gray-800 group-hover:text-violet-700 font-semibold transition-colors duration-200 flex-1">
                                            {{ $parentCategory->name }}
                                        </span>
                                        <span class="text-xs text-gray-500 group-hover:text-violet-600 transition-colors duration-200">
                                            ({{ $parentCategory->getTotalActiveProductsCount() }})
                                        </span>
                                    </div>
                                </div>

                                {{-- Children Categories (Nested) --}}
                                @if($parentCategory->children->count() > 0)
                                <div x-show="expanded" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="ml-8 mt-1 space-y-1"
                                     style="display: none;"
                                >
                                    @foreach($parentCategory->children as $childCategory)
                                    <div class="flex items-center gap-3 group py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                        <div class="relative">
                                            <input 
                                                type="checkbox" 
                                                wire:model.live="selectedCategories"
                                                value="{{ $childCategory->id }}"
                                                class="peer w-4 h-4 text-violet-600 border-2 border-gray-400 rounded focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer checked:border-violet-600"
                                            >
                                            <i class="fas fa-check absolute left-0.5 top-0.5 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity duration-200 pointer-events-none"></i>
                                        </div>
                                        <i class="fas fa-circle text-[4px] text-gray-400"></i>
                                        <span class="text-sm text-gray-700 group-hover:text-violet-700 font-medium transition-colors duration-200 flex-1">
                                            {{ $childCategory->name }}
                                        </span>
                                        <span class="text-xs text-gray-500 group-hover:text-violet-600 transition-colors duration-200">
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

                    {{-- Price Range Filter - Collapsible --}}
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <button 
                            @click="openPrice = !openPrice"
                            class="flex items-center justify-between w-full mb-4 group"
                        >
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-dollar-sign text-violet-600 text-sm"></i>
                                Price
                            </h3>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                               :class="openPrice ? 'rotate-180' : ''"
                            ></i>
                        </button>
                        
                        <div x-show="openPrice"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-3"
                        >
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <input 
                                        type="number" 
                                        wire:model.live.debounce.500ms="minPrice" 
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-medium focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all duration-200"
                                        placeholder="Min"
                                    >
                                </div>
                                <span class="text-gray-400 font-bold">—</span>
                                <div class="flex-1">
                                    <input 
                                        type="number" 
                                        wire:model.live.debounce.500ms="maxPrice" 
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-medium focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all duration-200"
                                        placeholder="Max"
                                    >
                                </div>
                            </div>
                            
                            @if((is_numeric($minPrice) && $minPrice > 0) || (is_numeric($maxPrice) && $maxPrice < 10000))
                            <div class="bg-violet-50 border border-violet-200 rounded-lg p-3 flex items-center justify-center gap-2">
                                <span class="text-sm font-bold text-violet-800">
                                    ${{ number_format(is_numeric($minPrice) ? $minPrice : 0, 0) }} - ${{ number_format(is_numeric($maxPrice) ? $maxPrice : 10000, 0) }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Rating Filter - Collapsible --}}
                    <div class="mb-2">
                        <button 
                            @click="openRating = !openRating"
                            class="flex items-center justify-between w-full mb-4 group"
                        >
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-star text-violet-600 text-sm"></i>
                                Customer Reviews
                            </h3>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                               :class="openRating ? 'rotate-180' : ''"
                            ></i>
                        </button>
                        
                        <div x-show="openRating"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-2"
                        >
                            @foreach([5, 4, 3, 2, 1] as $rating)
                            <label class="flex items-center gap-2 cursor-pointer group py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                <input 
                                    type="radio" 
                                    wire:model.live="selectedRating" 
                                    value="{{ $rating }}"
                                    class="w-4 h-4 text-violet-600 border-2 border-gray-400 focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer checked:border-violet-600"
                                >
                                <div class="flex items-center gap-1.5 flex-1">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-sm {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }} 
                                       group-hover:scale-110 transition-transform duration-200"></i>
                                    @endfor
                                    <span class="text-sm font-medium text-gray-800 group-hover:text-violet-700 ml-1 transition-colors duration-200">
                                        & Up
                                    </span>
                                </div>
                            </label>
                            @endforeach
                            
                            @if($selectedRating)
                            <button 
                                wire:click="$set('selectedRating', null)" 
                                class="text-xs text-violet-600 hover:text-violet-800 font-semibold mt-2 flex items-center gap-1 hover:underline transition-all duration-200"
                            >
                                <i class="fas fa-times text-xs"></i>
                                Clear Rating
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Mobile Off-Canvas Filter Panel --}}
        <div 
            x-show="showFilters"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 w-80 bg-white shadow-2xl z-50 lg:hidden overflow-y-auto"
            style="display: none;"
        >
            {{-- Mobile Filters Header with Close Button --}}
            <div class="px-6 py-4 border-b border-gray-300 bg-gray-50 sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-filter text-violet-600 mr-2"></i>
                        Filters
                    </h2>
                    <div class="flex items-center gap-3">
                        @if(!empty($selectedCategories) || $minPrice > 0 || $maxPrice < 10000 || $selectedRating)
                        <button 
                            wire:click="clearFilters" 
                            class="text-sm text-violet-600 hover:text-violet-800 font-semibold transition-all duration-200 hover:underline"
                        >
                            Clear All
                        </button>
                        @endif
                        <button 
                            @click="showFilters = false"
                            class="p-2 hover:bg-gray-200 rounded-lg transition-colors duration-200"
                            aria-label="Close filters"
                        >
                            <i class="fas fa-times text-xl text-gray-700"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile Filters Content (Same structure as Desktop) --}}
            <div class="px-6 py-4">
                {{-- Categories Filter - Nested Accordion --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <button 
                        @click="openCategories = !openCategories"
                        class="flex items-center justify-between w-full mb-4 group"
                    >
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-tag text-violet-600 text-sm"></i>
                            Categories
                        </h3>
                        <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                           :class="openCategories ? 'rotate-180' : ''"
                        ></i>
                    </button>
                    
                    <div x-show="openCategories" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="space-y-1 max-h-96 overflow-y-auto pr-1"
                    >
                        @foreach(\App\Models\Category::with('children')->whereNull('parent_id')->orderBy('order')->get() as $parentCategory)
                        <div x-data="{ expanded: false }" class="mb-1">
                            {{-- Parent Category --}}
                            <div class="flex items-center gap-2 py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                @if($parentCategory->children->count() > 0)
                                <button 
                                    @click="expanded = !expanded"
                                    class="p-1 hover:bg-violet-100 rounded transition-colors duration-200"
                                    type="button"
                                >
                                    <i class="fas fa-chevron-right text-xs text-gray-500 transition-transform duration-200"
                                       :class="expanded ? 'rotate-90' : ''"></i>
                                </button>
                                @else
                                <span class="w-6"></span>
                                @endif

                                <div class="flex items-center gap-3 group flex-1">
                                    <div class="relative">
                                        <input 
                                            type="checkbox" 
                                            wire:model.live="selectedCategories"
                                            value="{{ $parentCategory->id }}"
                                            class="peer w-4 h-4 text-violet-600 border-2 border-gray-400 rounded focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer checked:border-violet-600"
                                        >
                                        <i class="fas fa-check absolute left-0.5 top-0.5 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity duration-200 pointer-events-none"></i>
                                    </div>
                                    <span class="text-sm text-gray-800 group-hover:text-violet-700 font-semibold transition-colors duration-200 flex-1">
                                        {{ $parentCategory->name }}
                                    </span>
                                    <span class="text-xs text-gray-500 group-hover:text-violet-600 transition-colors duration-200">
                                        ({{ $parentCategory->getTotalActiveProductsCount() }})
                                    </span>
                                </div>
                            </div>

                            {{-- Children Categories (Nested) --}}
                            @if($parentCategory->children->count() > 0)
                            <div x-show="expanded" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="ml-8 mt-1 space-y-1"
                                 style="display: none;"
                            >
                                @foreach($parentCategory->children as $childCategory)
                                <div class="flex items-center gap-3 group py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                                    <div class="relative">
                                        <input 
                                            type="checkbox" 
                                            wire:model.live="selectedCategories"
                                            value="{{ $childCategory->id }}"
                                            class="peer w-4 h-4 text-violet-600 border-2 border-gray-400 rounded focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer checked:border-violet-600"
                                        >
                                        <i class="fas fa-check absolute left-0.5 top-0.5 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity duration-200 pointer-events-none"></i>
                                    </div>
                                    <i class="fas fa-circle text-[4px] text-gray-400"></i>
                                    <span class="text-sm text-gray-700 group-hover:text-violet-700 font-medium transition-colors duration-200 flex-1">
                                        {{ $childCategory->name }}
                                    </span>
                                    <span class="text-xs text-gray-500 group-hover:text-violet-600 transition-colors duration-200">
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

                {{-- Price Range Filter - Collapsible --}}
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <button 
                        @click="openPrice = !openPrice"
                        class="flex items-center justify-between w-full mb-4 group"
                    >
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-dollar-sign text-violet-600 text-sm"></i>
                            Price
                        </h3>
                        <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                           :class="openPrice ? 'rotate-180' : ''"
                        ></i>
                    </button>
                    
                    <div x-show="openPrice"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="space-y-3"
                    >
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <input 
                                    type="number" 
                                    wire:model.live.debounce.500ms="minPrice" 
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-medium focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all duration-200"
                                    placeholder="Min"
                                >
                            </div>
                            <span class="text-gray-400 font-bold">—</span>
                            <div class="flex-1">
                                <input 
                                    type="number" 
                                    wire:model.live.debounce.500ms="maxPrice" 
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-medium focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all duration-200"
                                    placeholder="Max"
                                >
                            </div>
                        </div>
                        
                        @if((is_numeric($minPrice) && $minPrice > 0) || (is_numeric($maxPrice) && $maxPrice < 10000))
                        <div class="bg-violet-50 border border-violet-200 rounded-lg p-3 flex items-center justify-center gap-2">
                            <span class="text-sm font-bold text-violet-800">
                                ${{ number_format(is_numeric($minPrice) ? $minPrice : 0, 0) }} - ${{ number_format(is_numeric($maxPrice) ? $maxPrice : 10000, 0) }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Rating Filter - Collapsible --}}
                <div class="mb-2">
                    <button 
                        @click="openRating = !openRating"
                        class="flex items-center justify-between w-full mb-4 group"
                    >
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-star text-violet-600 text-sm"></i>
                            Customer Reviews
                        </h3>
                        <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                           :class="openRating ? 'rotate-180' : ''"
                        ></i>
                    </button>
                    
                    <div x-show="openRating"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="space-y-2"
                    >
                        @foreach([5, 4, 3, 2, 1] as $rating)
                        <label class="flex items-center gap-2 cursor-pointer group py-1.5 px-2 rounded hover:bg-violet-50 transition-all duration-200">
                            <input 
                                type="radio" 
                                wire:model.live="selectedRating" 
                                value="{{ $rating }}"
                                class="w-4 h-4 text-violet-600 border-2 border-gray-400 focus:ring-2 focus:ring-violet-500 transition-all duration-200 cursor-pointer checked:border-violet-600"
                            >
                            <div class="flex items-center gap-1.5 flex-1">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-sm {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }} 
                                   group-hover:scale-110 transition-transform duration-200"></i>
                                @endfor
                                <span class="text-sm font-medium text-gray-800 group-hover:text-violet-700 ml-1 transition-colors duration-200">
                                    & Up
                                </span>
                            </div>
                        </label>
                        @endforeach
                        
                        @if($selectedRating)
                        <button 
                            wire:click="$set('selectedRating', null)" 
                            class="text-xs text-violet-600 hover:text-violet-800 font-semibold mt-2 flex items-center gap-1 hover:underline transition-all duration-200"
                        >
                            <i class="fas fa-times text-xs"></i>
                            Clear Rating
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Apply Button (Mobile Only) --}}
            <div class="sticky bottom-0 bg-white border-t border-gray-300 px-6 py-4">
                <button 
                    @click="showFilters = false"
                    class="w-full px-6 py-3 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700 transition-all duration-200 shadow-md hover:shadow-lg"
                >
                    <i class="fas fa-check mr-2"></i>
                    Apply Filters
                </button>
            </div>
        </div>

        {{-- Products Grid Area --}}
        <div class="lg:col-span-9 relative" style="z-index: 1;">
            {{-- Active Filters Display --}}
            @if(!empty($selectedCategories) || (is_numeric($minPrice) && $minPrice > 0) || (is_numeric($maxPrice) && $maxPrice < 10000) || $selectedRating)
            <div class="bg-violet-50 border border-violet-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-filter text-violet-600"></i>
                        Active Filters
                    </h4>
                    <button 
                        wire:click="clearFilters"
                        class="text-xs text-violet-600 hover:text-violet-800 font-semibold hover:underline transition-all duration-200"
                    >
                        <i class="fas fa-times-circle"></i> Clear All
                    </button>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    {{-- Category Filters --}}
                    @foreach($selectedCategories as $categoryId)
                        @php
                            $category = \App\Models\Category::find($categoryId);
                        @endphp
                        @if($category)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-violet-300 rounded-full text-sm font-medium text-gray-800 shadow-sm hover:shadow-md transition-all duration-200">
                            <i class="fas fa-tag text-violet-600 text-xs"></i>
                            {{ $category->name }}
                            <button 
                                wire:click="removeCategory({{ $categoryId }})"
                                class="ml-1 text-gray-500 hover:text-red-600 transition-colors duration-200"
                                type="button"
                            >
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </span>
                        @endif
                    @endforeach
                    
                    {{-- Price Filter --}}
                    @if((is_numeric($minPrice) && $minPrice > 0) || (is_numeric($maxPrice) && $maxPrice < 10000))
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-violet-300 rounded-full text-sm font-medium text-gray-800 shadow-sm hover:shadow-md transition-all duration-200">
                        <i class="fas fa-dollar-sign text-violet-600 text-xs"></i>
                        ${{ number_format(is_numeric($minPrice) ? $minPrice : 0, 0) }} - ${{ number_format(is_numeric($maxPrice) ? $maxPrice : 10000, 0) }}
                        <button 
                            wire:click="clearPriceFilter"
                            class="ml-1 text-gray-500 hover:text-red-600 transition-colors duration-200"
                            type="button"
                        >
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </span>
                    @endif
                    
                    {{-- Rating Filter --}}
                    @if($selectedRating)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-violet-300 rounded-full text-sm font-medium text-gray-800 shadow-sm hover:shadow-md transition-all duration-200">
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        {{ $selectedRating }}+ Stars
                        <button 
                            wire:click="$set('selectedRating', null)"
                            class="ml-1 text-gray-500 hover:text-red-600 transition-colors duration-200"
                        >
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
                        <button 
                            @click="showFilters = true"
                            class="lg:hidden flex items-center gap-2 px-4 py-2 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700 transition-all duration-200 shadow-sm hover:shadow-md"
                        >
                            <i class="fas fa-filter"></i>
                            <span>Filters</span>
                            @if(!empty($selectedCategories) || $minPrice > 0 || $maxPrice < 10000 || $selectedRating)
                            <span class="ml-1 px-2 py-0.5 bg-white text-violet-600 rounded-full text-xs font-bold">
                                {{ 
                                    count($selectedCategories) + 
                                    (($minPrice > 0 || $maxPrice < 10000) ? 1 : 0) + 
                                    ($selectedRating ? 1 : 0) 
                                }}
                            </span>
                            @endif
                        </button>

                        {{-- Results Count with Icon --}}
                        <div class="text-sm text-gray-700 flex items-center gap-2">
                            <i class="fas fa-box-open text-violet-600"></i>
                            <span class="hidden sm:inline">
                                Showing <span class="font-bold text-gray-900">{{ $products->firstItem() ?? 0 }}</span> 
                                to <span class="font-bold text-gray-900">{{ $products->lastItem() ?? 0 }}</span> 
                                of <span class="font-bold text-violet-700">{{ $products->total() }}</span> products
                            </span>
                            <span class="sm:hidden font-bold text-violet-700">
                                {{ $products->total() }} products
                            </span>
                        </div>
                    </div>

                    {{-- Enhanced Sort Dropdown --}}
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label class="text-sm text-gray-700 font-semibold whitespace-nowrap flex items-center gap-1.5">
                            <i class="fas fa-sort text-violet-600"></i>
                            Sort by:
                        </label>
                        <select 
                            wire:model.live="sortBy"
                            class="flex-1 sm:flex-none px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-medium focus:ring-2 focus:ring-violet-500 focus:border-violet-500 bg-white hover:border-violet-400 transition-all duration-200 cursor-pointer"
                        >
                            <option value="default">Default (Best Match)</option>
                            <option value="newest">Newest Arrivals</option>
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                            <option value="rating_desc">Highest Rated</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Products Grid --}}
            @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($products as $product)
                    <x-store.product-card :product="$product" />
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
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">No Products Found</h3>
                    <p class="text-gray-600 mb-8">
                        We couldn't find any products matching your filters. Try adjusting your search criteria.
                    </p>
                    <button 
                        wire:click="clearFilters"
                        class="inline-flex items-center gap-2 px-8 py-3.5 bg-violet-600 text-white rounded-lg hover:bg-violet-700 font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105"
                    >
                        <i class="fas fa-redo"></i>
                        Clear All Filters
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
