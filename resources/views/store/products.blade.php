<x-store-layout 
    title="Products - Violet Store"
    description="Browse our complete collection of quality products"
    keywords="products, online shopping, violet store"
>
    {{-- Breadcrumbs --}}
    <div class="bg-cream-100 py-4">
        <div class="container mx-auto px-4">
            <nav class="flex items-center gap-2 text-sm text-gray-600">
                <a href="/" class="hover:text-violet-600 transition">Home</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-900 font-medium">Products</span>
            </nav>
        </div>
    </div>

    {{-- Products Page Content --}}
    <div class="py-8">
        <div class="container mx-auto px-4">
            {{-- Page Header --}}
            <div class="mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2 font-serif">
                    All Products
                </h1>
                <p class="text-gray-600">
                    Discover our complete collection of premium products
                </p>
            </div>

            {{-- Products Grid with Sidebar --}}
            <livewire:store.product-list />
        </div>
    </div>
</x-store-layout>
