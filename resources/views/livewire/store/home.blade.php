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
                <a href="/products" class="inline-block bg-white text-purple-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                    {{ __('messages.shop_now') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Categories -->
    @if($categories->count() > 0)
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center">{{ __('messages.all_categories') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                @foreach($categories as $category)
                    <a href="/categories/{{ $category->slug }}" class="group">
                        <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition text-center">
                            <div class="mb-3 flex justify-center text-4xl text-violet-600">
                                @if($category->icon)
                                    @if(Str::startsWith($category->icon, 'heroicon'))
                                        @svg($category->icon, 'w-12 h-12')
                                    @else
                                        <div class="{{ $category->icon }}"></div>
                                    @endif
                                @else
                                    <span>📦</span>
                                @endif
                            </div>
                            <h3 class="font-semibold text-gray-800 group-hover:text-purple-600 transition">
                                {{ $category->name }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $category->products_count }} {{ app()->getLocale() === 'ar' ? 'منتج' : 'products' }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Featured Products -->
    @if($featuredProducts->count() > 0)
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">{{ __('messages.featured_products') }}</h2>
                <a href="/products?featured=1" class="text-purple-600 hover:text-purple-700 font-semibold">
                    {{ __('messages.view_all') }} →
                </a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
                    <div class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition group">
                        <div class="relative overflow-hidden aspect-square bg-gray-200">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-6xl">
                                    📦
                                </div>
                            @endif
                            @if($product->sale_price)
                                <span class="absolute top-2 {{ app()->getLocale() === 'ar' ? 'left-2' : 'right-2' }} bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                    -{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
                                </span>
                            @endif
                        </div>
                        
                        <div class="p-4">
                            <a href="/products/{{ $product->slug }}" class="font-semibold text-gray-800 hover:text-purple-600 line-clamp-2 mb-2">
                                {{ $product->name }}
                            </a>
                            
                            <div class="text-xs text-gray-500 mb-2">
                                {{ $product->category->name }}
                            </div>
                            
                            <div class="flex items-baseline gap-2 mb-3">
                                @if($product->sale_price)
                                    <span class="text-xl font-bold text-purple-600">
                                        {{ number_format($product->sale_price) }} {{ __('messages.egp') }}
                                    </span>
                                    <span class="text-sm text-gray-400 line-through">
                                        {{ number_format($product->price) }}
                                    </span>
                                @else
                                    <span class="text-xl font-bold text-purple-600">
                                        {{ number_format($product->price) }} {{ __('messages.egp') }}
                                    </span>
                                @endif
                            </div>
                            
                            <button class="w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                                {{ __('messages.add_to_cart') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- On Sale Products -->
    @if($onSaleProducts->count() > 0)
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">{{ __('messages.on_sale') }} 🔥</h2>
                <a href="/products?on_sale=1" class="text-purple-600 hover:text-purple-700 font-semibold">
                    {{ __('messages.view_all') }} →
                </a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($onSaleProducts as $product)
                    <div class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition group">
                        <div class="relative overflow-hidden aspect-square bg-gray-200">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-6xl">
                                    📦
                                </div>
                            @endif
                            <span class="absolute top-2 {{ app()->getLocale() === 'ar' ? 'left-2' : 'right-2' }} bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                -{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
                            </span>
                        </div>
                        
                        <div class="p-4">
                            <a href="/products/{{ $product->slug }}" class="font-semibold text-gray-800 hover:text-purple-600 line-clamp-2 mb-2">
                                {{ $product->name }}
                            </a>
                            
                            <div class="text-xs text-gray-500 mb-2">
                                {{ $product->category->name }}
                            </div>
                            
                            <div class="flex items-baseline gap-2 mb-3">
                                <span class="text-xl font-bold text-purple-600">
                                    {{ number_format($product->sale_price) }} {{ __('messages.egp') }}
                                </span>
                                <span class="text-sm text-gray-400 line-through">
                                    {{ number_format($product->price) }}
                                </span>
                            </div>
                            
                            <button class="w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                                {{ __('messages.add_to_cart') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</div>
