@props(['sections'])

@php($firstProductSection = true)
@foreach($sections as $category)
    <section class="py-16 {{ $loop->odd ? 'bg-gray-50' : 'bg-white' }}" data-home-category="{{ $category->slug }}">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-end gap-4 mb-10 border-b border-gray-200 pb-4">
                <h2 class="text-3xl font-bold text-gray-900 section-header">{{ $category->name }}</h2>
                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                    aria-label="{{ trans_db('storefront.view_all') }} {{ $category->name }}"
                    class="min-h-[44px] min-w-[44px] inline-flex items-center text-violet-600 hover:text-violet-800 font-bold">
                    {{ trans_db('storefront.view_all') }} &rarr;
                </a>
            </div>
            @if($category->products->isNotEmpty())
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 gap-y-10">
                    @foreach($category->products as $product)
                        @if($firstProductSection)
                            <x-store.product-card :product="$product" :aboveFold="$loop->iteration <= 4" />
                        @else
                            <x-store.product-card :product="$product" />
                        @endif
                    @endforeach
                </div>
                @php($firstProductSection = false)
            @else
                <p class="text-gray-600">{{ trans_db('storefront.empty_category') }}</p>
            @endif
        </div>
    </section>
@endforeach
