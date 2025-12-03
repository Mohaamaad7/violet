<div class="bg-cream-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.wishlist.title') }}</h1>
                <p class="mt-2 text-gray-600">
                    @if($items->count() > 0)
                        {{ trans_choice('messages.wishlist.items_count', $items->count(), ['count' => $items->count()]) }}
                    @else
                        {{ __('messages.wishlist.subtitle') }}
                    @endif
                </p>
            </div>
            
            @if($items->count() > 0)
                <button 
                    wire:click="clearWishlist"
                    wire:confirm="{{ __('messages.wishlist.clear_confirm') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-600 border border-red-600 rounded-lg hover:bg-red-50 transition-colors"
                >
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    {{ __('messages.wishlist.clear_all') }}
                </button>
            @endif
        </div>
        
        @if($items->isEmpty())
            {{-- Empty State --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-24 h-24 mx-auto mb-6 bg-red-50 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('messages.wishlist.empty') }}</h3>
                <p class="text-gray-500 mb-6">{{ __('messages.wishlist.empty_desc') }}</p>
                <a 
                    href="{{ route('products.index') }}" 
                    class="inline-flex items-center px-6 py-3 bg-violet-600 text-white font-semibold rounded-lg hover:bg-violet-700 transition-colors"
                >
                    <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    {{ __('messages.wishlist.browse_products') }}
                </a>
            </div>
        @else
            {{-- Wishlist Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($items as $item)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
                        {{-- Product Image --}}
                        <div class="relative aspect-square bg-gray-100">
                            @if($item->product)
                                <a href="{{ route('product.show', $item->product->slug) }}">
                                    @if($item->product->getFirstMediaUrl('images', 'thumb'))
                                        <img 
                                            src="{{ $item->product->getFirstMediaUrl('images', 'thumb') }}" 
                                            alt="{{ $item->product->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                                
                                {{-- Remove Button --}}
                                <button 
                                    wire:click="removeFromWishlist({{ $item->product_id }})"
                                    class="absolute top-3 end-3 p-2 bg-white rounded-full shadow-md text-gray-400 hover:text-red-500 transition-colors"
                                    title="{{ __('messages.wishlist.remove') }}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                
                                {{-- Stock Badge --}}
                                @if($item->product->stock <= 0)
                                    <div class="absolute bottom-3 start-3">
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">
                                            {{ __('messages.out_of_stock') }}
                                        </span>
                                    </div>
                                @elseif($item->product->stock <= 5)
                                    <div class="absolute bottom-3 start-3">
                                        <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">
                                            {{ __('messages.wishlist.low_stock', ['count' => $item->product->stock]) }}
                                        </span>
                                    </div>
                                @endif
                            @endif
                        </div>
                        
                        {{-- Product Info --}}
                        <div class="p-4">
                            @if($item->product)
                                <a href="{{ route('product.show', $item->product->slug) }}" class="block">
                                    <h3 class="font-semibold text-gray-900 truncate hover:text-violet-600 transition-colors">
                                        {{ $item->product->name }}
                                    </h3>
                                </a>
                                
                                {{-- Price --}}
                                <div class="flex items-center gap-2 mt-2">
                                    @if($item->product->compare_price && $item->product->compare_price > $item->product->price)
                                        <span class="text-lg font-bold text-red-600">
                                            {{ number_format($item->product->price, 2) }} {{ __('messages.egp') }}
                                        </span>
                                        <span class="text-sm text-gray-400 line-through">
                                            {{ number_format($item->product->compare_price, 2) }} {{ __('messages.egp') }}
                                        </span>
                                    @else
                                        <span class="text-lg font-bold text-gray-900">
                                            {{ number_format($item->product->price, 2) }} {{ __('messages.egp') }}
                                        </span>
                                    @endif
                                </div>
                                
                                {{-- Added Date --}}
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ __('messages.wishlist.added_on') }} {{ $item->created_at->format('M d, Y') }}
                                </p>
                                
                                {{-- Move to Cart Button --}}
                                <button 
                                    wire:click="moveToCart({{ $item->product_id }})"
                                    wire:loading.attr="disabled"
                                    @disabled($item->product->stock <= 0)
                                    class="w-full mt-4 py-3 px-4 inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition-colors
                                        {{ $item->product->stock > 0 
                                            ? 'bg-violet-600 text-white hover:bg-violet-700' 
                                            : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.class="animate-spin" wire:target="moveToCart({{ $item->product_id }})">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="moveToCart({{ $item->product_id }})">
                                        {{ $item->product->stock > 0 ? __('messages.wishlist.move_to_cart') : __('messages.out_of_stock') }}
                                    </span>
                                    <span wire:loading wire:target="moveToCart({{ $item->product_id }})">
                                        {{ __('messages.loading') }}
                                    </span>
                                </button>
                            @else
                                {{-- Product Deleted --}}
                                <div class="text-center py-4">
                                    <p class="text-gray-500">{{ __('messages.wishlist.product_unavailable') }}</p>
                                    <button 
                                        wire:click="removeFromWishlist({{ $item->product_id }})"
                                        class="mt-2 text-sm text-red-600 hover:text-red-800"
                                    >
                                        {{ __('messages.wishlist.remove') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
