<x-store-layout :title="$product->localized_meta_title ?: $product->localized_name"
    :keywords="$product->localized_meta_keywords"
    :description="$product->localized_meta_description ?: strip_tags($product->localized_short_description ?: $product->localized_description)">
    {{-- Breadcrumbs --}}
    <div class="bg-gray-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <x-store.breadcrumbs :items="[
                ['label' => trans_db('store.header.home'), 'url' => route('home')],
                ['label' => $product->category?->localized_name ?? trans_db('store.header.products'), 'url' => route('products.index')],
                ['label' => $product->localized_name, 'url' => null]
            ]" />
        </div>
    </div>

    {{-- Product Details Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @livewire('store.product-details', ['product' => $product])
    </div>
</x-store-layout>
