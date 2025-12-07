@props(['product'])

{{-- Minimalist Product Card - Compact & Elegant Design --}}
<div class="bg-white rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 group relative border border-gray-100" style="z-index: 1;">
    {{-- Product Image --}}
    <a href="{{ route('product.show', $product->slug) }}" class="block relative aspect-square bg-gray-50 overflow-hidden">
        @php
            // Get primary image from Spatie Media Library
            $primaryMedia = $product->getMedia('product-images')
                ->filter(fn($media) => $media->getCustomProperty('is_primary') === true)
                ->first();
            
            // Fallback to first media if no primary
            if (!$primaryMedia) {
                $primaryMedia = $product->getFirstMedia('product-images');
            }
            
            // Use 'card' conversion for better quality, fallback to original
            $imagePath = $primaryMedia 
                ? ($primaryMedia->hasGeneratedConversion('card') 
                    ? $primaryMedia->getUrl('card') 
                    : ($primaryMedia->hasGeneratedConversion('preview') 
                        ? $primaryMedia->getUrl('preview')
                        : $primaryMedia->getUrl()))
                : asset('images/default-product.png');
        @endphp
        
        <img 
            src="{{ $imagePath }}" 
            alt="{{ $product->name }}"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
        />
        
        {{-- Sale Badge - Minimal Style --}}
        @if($product->is_on_sale)
        <div class="absolute top-3 {{ app()->getLocale() === 'ar' ? 'right-3' : 'left-3' }} bg-red-500 text-white px-2 py-0.5 rounded text-xs font-bold z-10">
            -{{ $product->discount_percentage }}%
        </div>
        @endif
        
        {{-- Quick Actions Overlay --}}
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
            <div class="flex items-center gap-2">
                {{-- Quick View --}}
                <a href="{{ route('product.show', $product->slug) }}" 
                   class="w-10 h-10 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-violet-600 hover:text-white text-gray-700 transition-all duration-200 transform scale-90 group-hover:scale-100"
                   title="{{ __('store.product.view_details') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </a>
            </div>
        </div>
    </a>
    
    {{-- Product Info - Compact Padding --}}
    <div class="p-3">
        {{-- Category - Small Text --}}
        @if($product->category)
        <a href="{{ route('category.show', $product->category->slug) }}" class="text-[11px] text-gray-400 hover:text-violet-600 transition uppercase tracking-wide">
            {{ $product->category->name }}
        </a>
        @endif
        
        {{-- Product Name - Clean Typography --}}
        <h3 class="font-medium text-gray-800 text-sm mt-1 mb-2 line-clamp-2 leading-snug hover:text-violet-600 transition">
            <a href="{{ route('product.show', $product->slug) }}">
                {{ $product->name }}
            </a>
        </h3>
        
        {{-- Price Section - Compact --}}
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-1.5">
                @if($product->is_on_sale)
                <span class="text-base font-bold text-violet-600">
                    ${{ number_format($product->sale_price, 2) }}
                </span>
                <span class="text-xs text-gray-400 line-through">
                    ${{ number_format($product->price, 2) }}
                </span>
                @else
                <span class="text-base font-bold text-gray-900">
                    ${{ number_format($product->price, 2) }}
                </span>
                @endif
            </div>
            
            {{-- Stock Indicator - Minimal --}}
            @if($product->stock > 0)
            <span class="w-2 h-2 bg-green-500 rounded-full" title="{{ __('store.product_details.in_stock') }}"></span>
            @else
            <span class="w-2 h-2 bg-red-400 rounded-full" title="{{ __('store.product.out_of_stock') }}"></span>
            @endif
        </div>
        
        {{-- Actions Row --}}
        <div class="flex items-center gap-2" 
             x-data="{ adding: false }" 
             x-init="window.addEventListener('cart-count-updated', () => { adding = false });"
        >
            {{-- Add to Cart Button - Compact --}}
            <button 
                @if($product->stock > 0)
                @click="adding = true; window.Livewire.dispatch('add-to-cart', { productId: {{ $product->id }}, quantity: 1 })"
                @else
                disabled
                @endif
                :disabled="adding"
                class="flex-1 px-3 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700 transition disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed flex items-center justify-center gap-1.5"
            >
                <svg x-show="!adding" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <svg x-show="adding" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-xs" x-text="adding ? '' : '{{ __('store.product.add_to_cart') }}'"></span>
            </button>
            
            {{-- Wishlist Button --}}
            <livewire:store.wishlist-button :productId="$product->id" size="sm" :key="'wishlist-'.$product->id" />
        </div>
    </div>
</div>
