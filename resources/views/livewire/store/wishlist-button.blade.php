@php
    $sizeClasses = match($size) {
        'sm' => 'w-8 h-8 rounded-full',
        'lg' => 'px-6 py-4 rounded-lg',
        default => 'w-10 h-10 rounded-full',
    };
    
    $iconSizeClasses = match($size) {
        'sm' => 'w-4 h-4',
        'lg' => 'w-6 h-6',
        default => 'w-5 h-5',
    };
    
    $colorClasses = $inWishlist 
        ? ($size === 'lg' 
            ? 'bg-red-50 text-red-500 border-2 border-red-500 hover:bg-red-100' 
            : 'bg-red-50 text-red-500 hover:bg-red-100')
        : ($size === 'lg' 
            ? 'border-2 border-violet-600 text-violet-600 hover:bg-violet-50' 
            : 'bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-red-500');
@endphp

<button 
    wire:click="toggle"
    wire:loading.attr="disabled"
    class="group inline-flex items-center justify-center gap-2 {{ $sizeClasses }} transition-all duration-200 {{ $colorClasses }}"
    title="{{ $inWishlist ? __('messages.wishlist.remove') : __('messages.wishlist.add') }}"
>
    <svg 
        class="{{ $iconSizeClasses }} transition-transform group-hover:scale-110 {{ $inWishlist ? 'fill-current' : '' }}"
        fill="{{ $inWishlist ? 'currentColor' : 'none' }}"
        stroke="currentColor" 
        viewBox="0 0 24 24"
        wire:loading.class="animate-pulse"
    >
        <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
        ></path>
    </svg>
    
    @if($showText)
        <span class="text-sm font-medium">
            {{ $inWishlist ? __('messages.wishlist.saved') : __('messages.wishlist.save') }}
        </span>
    @endif
</button>
