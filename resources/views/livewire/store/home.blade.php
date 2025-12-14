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
            <div class="text-center mb-14">
                <h2 class="text-4xl font-bold text-gray-900 section-header mb-4">{{ __('messages.all_categories') }}</h2>
                <p class="text-gray-500 text-lg max-w-2xl mx-auto">{{ app()->getLocale() === 'ar' ? 'تصفح منتجاتنا المميزة حسب القسم وتجد ما يناسب ذوقك' : 'Browse through our premium categories and find your perfect match' }}</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                @foreach($categories as $category)
                    <a href="/categories/{{ $category->slug }}" class="group block h-full">
                        <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 transform group-hover:-translate-y-2 text-center h-full border border-gray-100 relative overflow-hidden">
                            <div class="absolute inset-0 bg-violet-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative z-10">
                                <div class="mb-4 flex justify-center text-5xl text-violet-600 transition-transform duration-300 group-hover:scale-110 drop-shadow-sm">
                                    @if($category->icon)
                                    @if(Str::startsWith($category->icon, 'heroicon'))
                                        @svg($category->icon, 'w-14 h-14')
                                    @else
                                        <div class="{{ $category->icon }}"></div>
                                    @endif
                                @else
                                    <span>📦</span>
                                @endif
                                </div>
                                <h3 class="font-bold text-lg text-gray-800 group-hover:text-violet-700 transition mb-2 font-heading">
                                    {{ $category->name }}
                                </h3>
                                <div class="w-12 h-1 bg-violet-100 group-hover:bg-violet-500 rounded-full mx-auto transition-colors duration-300 mb-3"></div>
                                <p class="text-sm text-gray-500 font-medium">
                                    {{ $category->products_count }} {{ app()->getLocale() === 'ar' ? 'منتج' : 'products' }}
                                </p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            
            <div class="mt-12 text-center">
                <a href="/categories" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-violet-600 bg-violet-100 hover:bg-violet-200 md:text-lg transition-colors duration-300">
                    {{ app()->getLocale() === 'ar' ? 'عرض جميع الأقسام' : 'View All Categories' }}
                </a>
            </div>
        </div>
    </section>
    @endif
</div>