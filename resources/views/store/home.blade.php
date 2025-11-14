<x-store-layout 
    title="Violet - Your Premium E-Commerce Destination"
    description="Shop quality products at unbeatable prices"
    keywords="online shopping, e-commerce, violet store"
>
    {{-- Hero Section / Sliders would go here --}}
    <div class="bg-gradient-to-r from-violet-600 to-violet-800 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
                Welcome to Violet Store
            </h1>
            <p class="text-xl md:text-2xl text-violet-100 mb-8 max-w-2xl mx-auto">
                Your premium destination for quality products at unbeatable prices
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/products" class="px-8 py-3 bg-white text-violet-700 rounded-lg font-semibold hover:bg-cream-100 transition">
                    Shop Now
                </a>
                <a href="/offers" class="px-8 py-3 bg-violet-700 text-white border-2 border-white rounded-lg font-semibold hover:bg-violet-800 transition">
                    View Offers
                </a>
            </div>
        </div>
    </div>

    {{-- Features Section --}}
    <div class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Free Shipping --}}
                <div class="text-center">
                    <div class="w-16 h-16 bg-violet-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Free Shipping</h3>
                    <p class="text-gray-600">On orders over $50</p>
                </div>

                {{-- Secure Payment --}}
                <div class="text-center">
                    <div class="w-16 h-16 bg-violet-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Secure Payment</h3>
                    <p class="text-gray-600">100% secure transactions</p>
                </div>

                {{-- Easy Returns --}}
                <div class="text-center">
                    <div class="w-16 h-16 bg-violet-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Easy Returns</h3>
                    <p class="text-gray-600">30-day return policy</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Featured Products Section (Placeholder) --}}
    <div class="py-16 bg-cream-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Featured Products
                </h2>
                <p class="text-gray-600 text-lg">
                    Discover our handpicked selection of premium products
                </p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @for($i = 1; $i <= 4; $i++)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group">
                    <div class="aspect-square bg-gray-200 flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-violet-600 transition">
                            Product Name {{ $i }}
                        </h3>
                        <p class="text-sm text-gray-500 mb-3">Category Name</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xl font-bold text-violet-600">$99.99</span>
                            <button class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700 transition">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                @endfor
            </div>

            <div class="text-center mt-10">
                <a href="/products" class="inline-block px-8 py-3 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700 transition">
                    View All Products
                </a>
            </div>
        </div>
    </div>

    {{-- Newsletter Section --}}
    <div class="py-16 bg-gradient-to-r from-violet-600 to-violet-800 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                Subscribe to Our Newsletter
            </h2>
            <p class="text-xl text-violet-100 mb-8 max-w-2xl mx-auto">
                Get exclusive offers, updates, and special deals delivered to your inbox
            </p>
            <form action="#" method="POST" class="max-w-md mx-auto flex gap-3">
                @csrf
                <input 
                    type="email" 
                    placeholder="Enter your email"
                    class="flex-1 px-4 py-3 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-white"
                    required
                >
                <button 
                    type="submit"
                    class="px-6 py-3 bg-white text-violet-700 rounded-lg font-semibold hover:bg-cream-100 transition"
                >
                    Subscribe
                </button>
            </form>
        </div>
    </div>
</x-store-layout>
