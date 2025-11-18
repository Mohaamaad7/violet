<x-store-layout>
    {{-- Breadcrumbs --}}
    <div class="bg-gray-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <x-store.breadcrumbs :items="[
                ['label' => 'Home', 'url' => route('home')],
                ['label' => $product->category->name ?? 'Products', 'url' => route('products.index')],
                ['label' => $product->name, 'url' => null]
            ]" />
        </div>
    </div>

    {{-- Product Details Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @livewire('store.product-details', ['product' => $product])
    </div>
</x-store-layout>
