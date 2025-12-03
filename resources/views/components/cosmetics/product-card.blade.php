{{-- Cosmetics Theme - Product Card --}}
@props([
    'product',
])

<div class="group bg-violet-900/50 rounded-2xl overflow-hidden border border-violet-800/50 hover:border-gold-400/30 transition-all duration-300 hover:transform hover:-translate-y-2">
    {{-- Product Image --}}
    <div class="relative aspect-square overflow-hidden bg-violet-900">
        @if($product->getFirstMediaUrl('images'))
            <img 
                src="{{ $product->getFirstMediaUrl('images', 'preview') }}" 
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                loading="lazy"
            >
        @else
            <div class="w-full h-full flex items-center justify-center bg-violet-800">
                <svg class="w-16 h-16 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif

        {{-- Quick Actions Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-t from-violet-950/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <div class="absolute bottom-4 inset-x-4 flex justify-center gap-3">
                {{-- Quick View --}}
                <a 
                    href="{{ route('product.show', $product->slug) }}"
                    class="p-3 bg-cream-100 text-violet-950 rounded-full hover:bg-gold-400 transition-colors duration-200"
                    title="{{ __('messages.cosmetics.product.quick_view') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>

                {{-- Add to Cart --}}
                <button 
                    wire:click="$dispatch('add-to-cart', { productId: {{ $product->id }}, quantity: 1 })"
                    class="p-3 bg-gold-400 text-violet-950 rounded-full hover:bg-gold-300 transition-colors duration-200"
                    title="{{ __('messages.cosmetics.product.add_to_cart') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </button>

                {{-- Wishlist Button --}}
                <livewire:store.wishlist-button :productId="$product->id" size="sm" :key="'cosmetics-wishlist-'.$product->id" />
            </div>
        </div>

        {{-- Sale Badge --}}
        @if($product->compare_price && $product->compare_price > $product->price)
            @php
                $discount = round((($product->compare_price - $product->price) / $product->compare_price) * 100);
            @endphp
            <div class="absolute top-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }} bg-gold-400 text-violet-950 text-xs font-bold px-2 py-1 rounded-full">
                -{{ $discount }}%
            </div>
        @endif

        {{-- Featured Badge --}}
        @if($product->is_featured)
            <div class="absolute top-4 {{ app()->getLocale() === 'ar' ? 'right-4' : 'left-4' }} bg-violet-600 text-cream-100 text-xs font-semibold px-2 py-1 rounded-full">
                {{ __('messages.cosmetics.product.featured') }}
            </div>
        @endif
    </div>

    {{-- Product Info --}}
    <div class="p-5">
        {{-- Category --}}
        @if($product->category)
            <p class="text-gold-400 text-xs font-medium uppercase tracking-wider mb-2">
                {{ $product->category->name }}
            </p>
        @endif

        {{-- Product Name --}}
        <h3 class="text-cream-100 font-semibold text-lg mb-2 line-clamp-2 group-hover:text-gold-400 transition-colors duration-200">
            <a href="{{ route('product.show', $product->slug) }}">
                {{ $product->name }}
            </a>
        </h3>

        {{-- Short Description --}}
        @if($product->short_description)
            <p class="text-cream-400 text-sm mb-4 line-clamp-2">
                {{ $product->short_description }}
            </p>
        @endif

        {{-- Price --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-cream-100">
                    {{ Number::currency($product->price, 'SAR') }}
                </span>
                @if($product->compare_price && $product->compare_price > $product->price)
                    <span class="text-sm text-cream-500 line-through">
                        {{ Number::currency($product->compare_price, 'SAR') }}
                    </span>
                @endif
            </div>

            {{-- Stock Status --}}
            @if($product->stock_quantity > 0)
                <span class="text-xs text-green-400">{{ __('messages.cosmetics.product.in_stock') }}</span>
            @else
                <span class="text-xs text-red-400">{{ __('messages.cosmetics.product.out_of_stock') }}</span>
            @endif
        </div>
    </div>
</div>
