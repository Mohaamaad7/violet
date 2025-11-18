@props(['product'])

<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 group relative" style="z-index: 1;">
    {{-- Product Image --}}
    <a href="{{ route('product.show', $product->slug) }}" class="block relative aspect-square bg-gray-100 overflow-hidden">
        @php
            // Get primary image from Spatie Media Library
            $primaryMedia = $product->getMedia('product-images')
                ->filter(fn($media) => $media->getCustomProperty('is_primary') === true)
                ->first();
            
            // Fallback to first media if no primary
            if (!$primaryMedia) {
                $primaryMedia = $product->getFirstMedia('product-images');
            }
            
            // Use placeholder if no media exists
            // Try thumbnail conversion, fallback to original if conversion doesn't exist
            $imagePath = $primaryMedia 
                ? ($primaryMedia->hasGeneratedConversion('thumbnail') 
                    ? $primaryMedia->getUrl('thumbnail') 
                    : $primaryMedia->getUrl())
                : asset('images/default-product.png');
        @endphp
        
        <img 
            src="{{ $imagePath }}" 
            alt="{{ $product->name }}"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            loading="lazy"
        />
        
        {{-- Sale Badge --}}
        @if($product->is_on_sale)
        <div class="absolute top-2 left-2 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold z-10">
            -{{ $product->discount_percentage }}%
        </div>
        @endif
        
        {{-- Quick View Icon Button (Top-Right Corner) --}}
        <a href="{{ route('product.show', $product->slug) }}" 
           class="absolute top-2 right-2 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-violet-600 hover:text-white text-violet-600 z-10"
           title="Quick View">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </a>
    </a>
    
    {{-- Product Info --}}
    <div class="p-4">
        {{-- Category Badge --}}
        @if($product->category)
        <a href="{{ route('category.show', $product->category->slug) }}" class="inline-block text-xs text-violet-600 hover:text-violet-700 font-medium mb-2">
            {{ $product->category->name }}
        </a>
        @endif
        
        {{-- Product Name --}}
        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 hover:text-violet-600 transition">
            <a href="{{ route('product.show', $product->slug) }}">
                {{ $product->name }}
            </a>
        </h3>
        
        {{-- Price Section --}}
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                {{-- Sale Price --}}
                @if($product->is_on_sale)
                <span class="text-xl font-bold text-violet-600">
                    ${{ number_format($product->sale_price, 2) }}
                </span>
                <span class="text-sm text-gray-500 line-through">
                    ${{ number_format($product->price, 2) }}
                </span>
                @else
                <span class="text-xl font-bold text-violet-600">
                    ${{ number_format($product->price, 2) }}
                </span>
                @endif
            </div>
            
            {{-- Stock Status --}}
            @if($product->stock > 0)
            <span class="text-xs text-green-600 font-medium">In Stock</span>
            @else
            <span class="text-xs text-red-600 font-medium">Out of Stock</span>
            @endif
        </div>
        
        {{-- Actions --}}
        <div class="flex items-center gap-2" 
             x-data="{ adding: false }" 
             x-init="window.addEventListener('cart-count-updated', () => { adding = false });"
        >
            {{-- Add to Cart Button --}}
            <button 
                @if($product->stock > 0)
                @click="adding = true; window.Livewire.dispatch('add-to-cart', { productId: {{ $product->id }}, quantity: 1 })"
                @else
                disabled
                @endif
                :disabled="adding"
                class="flex-1 px-4 py-2 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                <svg x-show="!adding" class="inline-block w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <svg x-show="adding" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span x-text="adding ? 'Adding...' : (@js($product->stock > 0 ? 'Add to Cart' : 'Sold Out'))"></span>
            </button>
            
            {{-- Wishlist Button --}}
            <button 
                wire:click="$dispatch('add-to-wishlist', { productId: {{ $product->id }} })"
                class="px-3 py-2 border-2 border-violet-600 text-violet-600 rounded-lg hover:bg-violet-50 transition"
                title="Add to Wishlist"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
        </div>
    </div>
</div>
