{{-- Partners Sidebar - Enhanced Navigation --}}
<aside id="sidebar" 
       class="fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} z-50 w-64 bg-white dark:bg-gray-900 border-{{ app()->getLocale() === 'ar' ? 'l' : 'r' }} border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto shadow-lg lg:shadow-none"
       :class="sidebarOpen ? 'translate-x-0' : 'max-lg:{{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}'">
    
    {{-- Logo Section --}}
    <div class="flex items-center justify-between h-20 border-b border-gray-100 dark:border-gray-800 px-6">
        <div class="flex items-center gap-3">
            {{-- Logo Icon --}}
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            
            {{-- Brand Name --}}
            <div>
                <p class="font-bold text-gray-900 dark:text-white text-sm">Flower Violet</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Partners') }}</p>
            </div>
        </div>
        
        {{-- Close Button (Mobile Only) --}}
        <button 
            @click="sidebarOpen = false"
            class="lg:hidden p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    
    {{-- Navigation Links --}}
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto no-scrollbar">
        
        {{-- Dashboard --}}
        <a href="{{ route('filament.partners.pages.influencer-dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('filament.partners.pages.influencer-dashboard') ? 'text-white bg-gradient-to-r from-violet-600 to-purple-600 shadow-lg shadow-violet-500/30' : 'text-gray-700 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-gray-800 hover:text-violet-700 dark:hover:text-violet-400' }} rounded-lg transition-all duration-200 group">
            <svg class="w-5 h-5 {{ request()->routeIs('filament.partners.pages.influencer-dashboard') ? '' : 'group-hover:scale-110 transition-transform' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="font-medium text-sm">{{ __('messages.partners.nav.dashboard') }}</span>
        </a>
        
        {{-- Profile --}}
        <a href="{{ route('filament.partners.pages.profile-page') }}" 
           class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('filament.partners.pages.profile-page') ? 'text-white bg-gradient-to-r from-violet-600 to-purple-600 shadow-lg shadow-violet-500/30' : 'text-gray-700 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-gray-800 hover:text-violet-700 dark:hover:text-violet-400' }} rounded-lg transition-all duration-200 group">
            <svg class="w-5 h-5 {{ request()->routeIs('filament.partners.pages.profile-page') ? '' : 'group-hover:scale-110 transition-transform' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="font-medium text-sm">{{ __('messages.partners.nav.profile') }}</span>
        </a>
        
        {{-- Commissions --}}
        <a href="{{ route('filament.partners.pages.commissions-page') }}" 
           class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('filament.partners.pages.commissions-page') ? 'text-white bg-gradient-to-r from-violet-600 to-purple-600 shadow-lg shadow-violet-500/30' : 'text-gray-700 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-gray-800 hover:text-violet-700 dark:hover:text-violet-400' }} rounded-lg transition-all duration-200 group">
            <svg class="w-5 h-5 {{ request()->routeIs('filament.partners.pages.commissions-page') ? '' : 'group-hover:scale-110 transition-transform' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium text-sm">{{ __('messages.partners.nav.commissions') }}</span>
        </a>
        
        {{-- Discount Codes --}}
        <a href="{{ route('filament.partners.pages.discount-codes-page') }}" 
           class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('filament.partners.pages.discount-codes-page') ? 'text-white bg-gradient-to-r from-violet-600 to-purple-600 shadow-lg shadow-violet-500/30' : 'text-gray-700 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-gray-800 hover:text-violet-700 dark:hover:text-violet-400' }} rounded-lg transition-all duration-200 group">
            <svg class="w-5 h-5 {{ request()->routeIs('filament.partners.pages.discount-codes-page') ? '' : 'group-hover:scale-110 transition-transform' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <span class="font-medium text-sm">{{ __('messages.partners.nav.discount_codes') }}</span>
        </a>
        
        {{-- Payouts --}}
        <a href="{{ route('filament.partners.pages.payouts-page') }}" 
           class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('filament.partners.pages.payouts-page') ? 'text-white bg-gradient-to-r from-violet-600 to-purple-600 shadow-lg shadow-violet-500/30' : 'text-gray-700 dark:text-gray-300 hover:bg-violet-50 dark:hover:bg-gray-800 hover:text-violet-700 dark:hover:text-violet-400' }} rounded-lg transition-all duration-200 group">
            <svg class="w-5 h-5 {{ request()->routeIs('filament.partners.pages.payouts-page') ? '' : 'group-hover:scale-110 transition-transform' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="font-medium text-sm">{{ __('messages.partners.nav.payouts') }}</span>
        </a>
        
        {{-- Divider --}}
        <div class="py-3">
            <div class="border-t border-gray-200 dark:border-gray-700"></div>
        </div>
        
        {{-- Logout --}}
        <form method="POST" action="{{ route('filament.partners.auth.logout') }}">
            @csrf
            <button type="submit" 
                    class="w-full flex items-center gap-3 px-3 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-all duration-200 group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="font-medium text-sm">{{ __('messages.partners.nav.logout') }}</span>
            </button>
        </form>
    </nav>
    
    {{-- Footer Info --}}
    <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3 px-3 py-2 bg-violet-50 dark:bg-violet-900/20 rounded-lg">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                {{ mb_substr(auth()->user()->name, 0, 2) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Influencer') }}
                </p>
            </div>
        </div>
    </div>
</aside>
