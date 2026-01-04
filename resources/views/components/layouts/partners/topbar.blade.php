{{-- Partners Topbar - Clean Design Without Filament --}}
<header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 h-16 flex items-center justify-between px-4 md:px-6 shrink-0">
    
    {{-- Right Side (Mobile Menu + Heading) --}}
    <div class="flex items-center gap-3">
        {{-- Mobile Menu Button (Hidden on Desktop) --}}
        <button 
            @click="sidebarOpen = !sidebarOpen"
            type="button"
            class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            aria-label="فتح القائمة"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Page Heading --}}
        <h1 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white">
            {{ $heading ?? __('messages.partners.dashboard.title') }}
        </h1>
    </div>

    {{-- Left Side (User Dropdown) --}}
    <div class="relative" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">
        {{-- Avatar Button --}}
        <button 
            @click="userMenuOpen = !userMenuOpen"
            type="button"
            class="flex items-center gap-2 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            aria-label="قائمة المستخدم"
        >
            <div class="w-9 h-9 rounded-full bg-violet-600 text-white flex items-center justify-center font-bold text-sm">
                {{ mb_substr(auth()->user()->name, 0, 2) }}
            </div>
        </button>

        {{-- Dropdown Menu --}}
        <div 
            x-show="userMenuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute top-full {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50 overflow-hidden"
            style="display: none;"
            @click="userMenuOpen = false"
        >
            {{-- User Info Header --}}
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <p class="font-semibold text-gray-900 dark:text-white text-sm">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ auth()->user()->email }}
                </p>
            </div>

            {{-- Menu Items --}}
            <div class="p-2">
                {{-- Profile Link --}}
                <a 
                    href="{{ route('filament.partners.pages.profile-page') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-sm font-medium">{{ __('messages.partners.nav.profile') }}</span>
                </a>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('filament.partners.auth.logout') }}">
                    @csrf
                    <button 
                        type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="text-sm font-medium">{{ __('messages.partners.nav.logout') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
