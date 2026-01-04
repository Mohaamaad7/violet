{{-- Partners Sidebar --}}
<aside id="sidebar" 
       class="fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} z-50 w-64 bg-white dark:bg-gray-900 border-{{ app()->getLocale() === 'ar' ? 'l' : 'r' }} border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto"
       :class="sidebarOpen ? 'translate-x-0' : '{{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}'">
    
    {{-- Logo --}}
    <div class="flex items-center justify-center h-20 border-b border-gray-100 dark:border-gray-800 px-4">
        <div class="flex items-center gap-2 text-primary-700 dark:text-primary-400 font-bold text-xl">
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <span>Flower Violet</span>
        </div>
    </div>
    
    {{-- Navigation Links --}}
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto no-scrollbar">
        
        <a href="{{ route('filament.partners.pages.influencer-dashboard') }}" 
           class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('filament.partners.pages.influencer-dashboard') ? 'text-white bg-primary-600 dark:bg-primary-600 shadow-md shadow-primary-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-700 dark:hover:text-primary-400' }} rounded-xl transition-all group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="font-semibold">{{ __('messages.partners.nav.dashboard') }}</span>
        </a>
        
        <a href="{{ route('filament.partners.pages.profile-page') }}" 
           class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('filament.partners.pages.profile-page') ? 'text-white bg-primary-600 dark:bg-primary-600 shadow-md shadow-primary-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-700 dark:hover:text-primary-400' }} rounded-xl transition-colors group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="font-medium">{{ __('messages.partners.nav.profile') }}</span>
        </a>
        
        <a href="{{ route('filament.partners.pages.commissions-page') }}" 
           class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('filament.partners.pages.commissions-page') ? 'text-white bg-primary-600 dark:bg-primary-600 shadow-md shadow-primary-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-700 dark:hover:text-primary-400' }} rounded-xl transition-colors group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="font-medium">{{ __('messages.partners.nav.commissions') }}</span>
        </a>
        
        <a href="{{ route('filament.partners.pages.discount-codes-page') }}" 
           class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('filament.partners.pages.discount-codes-page') ? 'text-white bg-primary-600 dark:bg-primary-600 shadow-md shadow-primary-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-700 dark:hover:text-primary-400' }} rounded-xl transition-colors group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
            <span class="font-medium">{{ __('messages.partners.nav.discount_codes') }}</span>
        </a>
        
        <a href="{{ route('filament.partners.pages.payouts-page') }}" 
           class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('filament.partners.pages.payouts-page') ? 'text-white bg-primary-600 dark:bg-primary-600 shadow-md shadow-primary-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-700 dark:hover:text-primary-400' }} rounded-xl transition-colors group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="font-medium">{{ __('messages.partners.nav.payouts') }}</span>
        </a>
        
        {{-- Settings Section --}}
        <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-800">
            <p class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">
                {{ __('messages.partners.nav.settings') }}
            </p>
            
            <form method="POST" action="{{ route('filament.partners.auth.logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-600 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-xl transition-colors group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="font-medium">{{ __('messages.partners.nav.logout') }}</span>
                </button>
            </form>
        </div>
    </nav>
</aside>
