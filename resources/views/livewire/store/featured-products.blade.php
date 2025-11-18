{{-- Featured Products Section --}}
<div class="py-16 bg-cream-50">
    <div class="container mx-auto px-4">
        {{-- Section Header --}}
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 font-serif">
                Featured Products
            </h2>
            <p class="text-gray-600 text-lg">
                Discover our handpicked selection of premium products
            </p>
        </div>
        
        {{-- Products Grid --}}
        @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                <x-store.product-card :product="$product" />
            @endforeach
        </div>
        
        {{-- View All Button --}}
        <div class="text-center mt-12">
            <a 
                href="/products?featured=1" 
                class="inline-flex items-center px-8 py-3 bg-violet-600 text-white font-semibold rounded-lg hover:bg-violet-700 transition shadow-lg"
            >
                View All Featured Products
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
        @else
        {{-- Empty State --}}
        <div class="text-center py-12">
            <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Featured Products Yet</h3>
            <p class="text-gray-500 mb-6">Check back soon for our handpicked selection!</p>
            <a href="/products" class="inline-block px-6 py-3 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition">
                Browse All Products
            </a>
        </div>
        @endif
    </div>
</div>

