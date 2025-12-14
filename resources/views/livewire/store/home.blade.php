<div>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl {{ app()->getLocale() === 'ar' ? 'mr-auto' : 'mx-auto' }} text-center">
                <h1 class="text-5xl font-bold mb-6">
                    {{ __('messages.welcome') }}
                </h1>
                <p class="text-xl mb-8 text-purple-100">
                    {{ app()->getLocale() === 'ar' ? 'اكتشف أفضل المنتجات بأفضل الأسعار' : 'Discover the best products at the best prices' }}
                </p>
                <a href="/products"
                    class="inline-block bg-white text-purple-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition shadow-lg">
                    {{ __('messages.shop_now') }}
                </a>
            </div>
        </div>
    </section>

    <!-- 2. Featured Products -->
    @if($featuredProducts->count() > 0)
        <section class="py-12 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ __('messages.featured_products') ?? 'Featured Products' }}</h2>
                        <p class="text-gray-500 text-sm mt-1">
                            {{ app()->getLocale() === 'ar' ? 'اختياراتنا المميزة لك' : 'Our handpicked selection for you' }}
                        </p>
                    </div>
                    <a href="/products?featured=1"
                        class="text-purple-600 hover:text-purple-700 font-semibold text-sm flex items-center gap-1">
                        {{ __('messages.view_all') }} <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($featuredProducts as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- 3. New Arrivals -->
    @if($newArrivals->count() > 0)
        <section class="py-12">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ __('messages.new_arrivals') ?? 'New Arrivals' }}
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">
                            {{ app()->getLocale() === 'ar' ? 'أحدث المنتجات المضافة' : 'Specifically sorted for you' }}</p>
                    </div>
                    <a href="/products?sort=latest"
                        class="text-purple-600 hover:text-purple-700 font-semibold text-sm flex items-center gap-1">
                        {{ __('messages.view_all') }} <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($newArrivals as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- 4. Best Deals (On Sale) -->
    @if($onSaleProducts->count() > 0)
        <section class="py-12 bg-purple-50">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center mb-8">
                    <div class="flex items-center gap-2">
                        <h2 class="text-2xl font-bold text-gray-800">{{ __('messages.on_sale') ?? 'Best Deals' }}</h2>
                        <span class="bg-red-100 text-red-600 px-2 py-0.5 rounded text-xs font-bold">Hot</span>
                    </div>
                    <a href="/products?on_sale=1"
                        class="text-purple-600 hover:text-purple-700 font-semibold text-sm flex items-center gap-1">
                        {{ __('messages.view_all') }} <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($onSaleProducts as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- 5. Shop by Category -->
    @if($categories->count() > 0)
        <section class="py-16">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold mb-10 text-center">{{ __('messages.all_categories') }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                    @foreach($categories as $category)
                        <a href="/categories/{{ $category->slug }}" class="group block h-full">
                            <div
                                class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 transform group-hover:-translate-y-1 text-center h-full border border-gray-100">
                                <div
                                    class="mb-4 flex justify-center text-5xl text-violet-600 transition-transform duration-300 group-hover:scale-110">
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
                                <h3 class="font-bold text-gray-800 group-hover:text-purple-600 transition mb-2">
                                    {{ $category->name }}
                                </h3>
                                <div
                                    class="w-8 h-1 bg-purple-100 group-hover:bg-purple-500 rounded-full mx-auto transition-colors duration-300">
                                </div>
                                <p class="text-xs text-gray-500 mt-2 font-medium">
                                    {{ $category->products_count }} {{ app()->getLocale() === 'ar' ? 'منتج' : 'products' }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>