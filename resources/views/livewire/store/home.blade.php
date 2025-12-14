<div>
    <!-- Hero Section -->
    <livewire:store.hero-slider />

    <!-- 2. Featured Products -->
    @if($featuredProducts->count() > 0)
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-end mb-10 border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 section-header">{{ __('messages.featured_products') ?? 'Featured Products' }}</h2>
                    <p class="text-gray-500 mt-3 text-lg">{{ app()->getLocale() === 'ar' ? 'اختياراتنا المميزة لك من أفضل المنتجات' : 'Our handpicked selection of premium products' }}</p>
                </div>
                <a href="/products?featured=1" class="text-violet-600 hover:text-violet-800 font-bold flex items-center gap-2 transition-colors duration-300 mb-1 group">
                    <span>{{ __('messages.view_all') }}</span>
                    <span class="transform group-hover:translate-x-1 {{ app()->getLocale() === 'ar' ? 'group-hover:-translate-x-1' : '' }} transition-transform">&rarr;</span>
                </a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 gap-y-10">
                @foreach($featuredProducts as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- 3. New Arrivals -->
    @if($newArrivals->count() > 0)
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-end mb-10 border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 section-header">{{ __('messages.new_arrivals') ?? 'New Arrivals' }}</h2>
                    <p class="text-gray-500 mt-3 text-lg">{{ app()->getLocale() === 'ar' ? 'أحدث المنتجات التي وصلت لمتجرنا' : 'Fresh styling updates just for you' }}</p>
                </div>
                <a href="/products?sort=latest" class="text-violet-600 hover:text-violet-800 font-bold flex items-center gap-2 transition-colors duration-300 mb-1 group">
                    <span>{{ __('messages.view_all') }}</span>
                    <span class="transform group-hover:translate-x-1 {{ app()->getLocale() === 'ar' ? 'group-hover:-translate-x-1' : '' }} transition-transform">&rarr;</span>
                </a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 gap-y-10">
                @foreach($newArrivals as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- 4. Best Deals (On Sale) -->
    @if($onSaleProducts->count() > 0)
    <section class="py-16 bg-gradient-to-b from-purple-50 to-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10 border-b border-purple-200 pb-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-3xl font-bold text-gray-900 section-header">{{ __('messages.on_sale') ?? 'Best Deals' }}</h2>
                    <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold animate-pulse shadow-md">
                        🔥 {{ app()->getLocale() === 'ar' ? 'عروض ساخنة' : 'Hot Offers' }}
                    </span>
                </div>
                <a href="/products?on_sale=1" class="text-violet-600 hover:text-violet-800 font-bold flex items-center gap-2 transition-colors duration-300 group">
                    <span>{{ __('messages.view_all') }}</span>
                    <span class="transform group-hover:translate-x-1 {{ app()->getLocale() === 'ar' ? 'group-hover:-translate-x-1' : '' }} transition-transform">&rarr;</span>
                </a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 gap-y-10">
                @foreach($onSaleProducts as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- 5. Shop by Category -->
    @if($categories->count() > 0)
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-900 section-header mb-2">{{ __('messages.all_categories') }}</h2>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8 justify-center max-w-7xl mx-auto">
                @foreach($categories as $category)
                    <a href="/categories/{{ $category->slug }}" class="group block text-center">
                        <div class="relative overflow-hidden rounded-full aspect-square mb-4 shadow-sm group-hover:shadow-md transition-all duration-300 border border-gray-100 bg-gray-50 w-4/5 mx-auto">
                            @php
                                $imageUrl = $category->getFirstMediaUrl('category-images', 'card');
                                if (!$imageUrl) {
                                    $imageUrl = asset('images/default-category.png'); 
                                }
                            @endphp
                            
                            @if($category->hasMedia('category-images'))
                                <img src="{{ $imageUrl }}" 
                                     alt="{{ $category->name }}" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-violet-50 text-violet-300 group-hover:text-violet-500 transition-colors">
                                    @if($category->icon && Str::startsWith($category->icon, 'heroicon'))
                                        @svg($category->icon, 'w-12 h-12')
                                    @else
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <h3 class="font-bold text-base text-gray-900 group-hover:text-violet-700 transition font-sans">
                            {{ $category->name }} &rarr;
                        </h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</div>