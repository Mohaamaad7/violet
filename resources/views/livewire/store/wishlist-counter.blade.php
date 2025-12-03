<a 
    href="{{ route('wishlist') }}" 
    class="relative p-2 hover:bg-gray-100 rounded-lg transition group"
    title="{{ __('messages.wishlist.title') }}"
>
    <svg class="w-6 h-6 text-gray-700 group-hover:text-violet-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
    </svg>
    
    @if($count > 0)
        <span 
            class="absolute -top-1 -right-1 bg-violet-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"
        >
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</a>
