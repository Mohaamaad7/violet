{{-- Cosmetics Theme - Glass Navigation Bar --}}
<nav class="glass-nav fixed top-0 left-0 right-0 z-50 border-b border-violet-800/50" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                <a href="{{ route('cosmetics.home') }}" class="flex items-center gap-2">
                    <span class="text-2xl font-playfair font-bold text-gold-400">{{ config('app.name', 'Violet') }}</span>
                </a>
            </div>

            {{-- Desktop Navigation --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ route('cosmetics.home') }}" class="text-cream-100 hover:text-gold-400 transition-colors duration-200 font-medium">
                    {{ __('messages.cosmetics.nav.home') }}
                </a>
                <a href="{{ route('products.index') }}" class="text-cream-100 hover:text-gold-400 transition-colors duration-200 font-medium">
                    {{ __('messages.cosmetics.nav.products') }}
                </a>
                <a href="#best-sellers" class="text-cream-100 hover:text-gold-400 transition-colors duration-200 font-medium">
                    {{ __('messages.cosmetics.nav.best_sellers') }}
                </a>
                <a href="#contact" class="text-cream-100 hover:text-gold-400 transition-colors duration-200 font-medium">
                    {{ __('messages.cosmetics.nav.contact') }}
                </a>
            </div>

            {{-- Right Side Actions --}}
            <div class="flex items-center gap-4">
                {{-- Language Switcher --}}
                <div class="relative" x-data="{ open: false }">
                    <button 
                        @click="open = !open" 
                        @click.outside="open = false"
                        class="flex items-center gap-1 text-cream-100 hover:text-gold-400 transition-colors duration-200"
                    >
                        <span class="text-sm font-medium">{{ app()->getLocale() === 'ar' ? 'عربي' : 'EN' }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div 
                        x-show="open" 
                        x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-32 bg-violet-900 border border-violet-700 rounded-lg shadow-lg overflow-hidden"
                    >
                        <a href="{{ route('locale.switch', 'en') }}" class="block px-4 py-2 text-sm text-cream-100 hover:bg-violet-800 {{ app()->getLocale() === 'en' ? 'bg-violet-800' : '' }}">
                            English
                        </a>
                        <a href="{{ route('locale.switch', 'ar') }}" class="block px-4 py-2 text-sm text-cream-100 hover:bg-violet-800 {{ app()->getLocale() === 'ar' ? 'bg-violet-800' : '' }}">
                            العربية
                        </a>
                    </div>
                </div>

                {{-- Cart Button --}}
                <button 
                    @click="$dispatch('toggle-cart')"
                    class="relative p-2 text-cream-100 hover:text-gold-400 transition-colors duration-200"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    {{-- Cart Count Badge --}}
                    <span 
                        x-data="{ count: 0 }"
                        x-on:cart-updated.window="count = $event.detail.count"
                        x-show="count > 0"
                        x-text="count"
                        x-cloak
                        class="absolute -top-1 -right-1 w-5 h-5 bg-gold-400 text-violet-950 text-xs font-bold rounded-full flex items-center justify-center"
                    ></span>
                </button>

                {{-- User Menu / Auth --}}
                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open" 
                            @click.outside="open = false"
                            class="flex items-center gap-2 text-cream-100 hover:text-gold-400 transition-colors duration-200"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </button>
                        <div 
                            x-show="open" 
                            x-cloak
                            x-transition
                            class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-48 bg-violet-900 border border-violet-700 rounded-lg shadow-lg overflow-hidden"
                        >
                            <div class="px-4 py-3 border-b border-violet-700">
                                <p class="text-sm text-cream-100 font-medium">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-cream-400 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('store.orders.index') }}" class="block px-4 py-2 text-sm text-cream-100 hover:bg-violet-800">
                                {{ __('messages.store.my_orders') }}
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-start px-4 py-2 text-sm text-cream-100 hover:bg-violet-800">
                                    {{ __('messages.store.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:block text-cream-100 hover:text-gold-400 transition-colors duration-200 font-medium">
                        {{ __('messages.store.login') }}
                    </a>
                @endauth

                {{-- Mobile Menu Button --}}
                <button 
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 text-cream-100 hover:text-gold-400"
                >
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div 
        x-show="mobileMenuOpen" 
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="md:hidden bg-violet-900/95 backdrop-blur-md border-t border-violet-800"
    >
        <div class="px-4 py-4 space-y-3">
            <a href="{{ route('cosmetics.home') }}" class="block py-2 text-cream-100 hover:text-gold-400 transition-colors duration-200 font-medium">
                {{ __('messages.cosmetics.nav.home') }}
            </a>
            <a href="{{ route('products.index') }}" class="block py-2 text-cream-100 hover:text-gold-400 transition-colors duration-200 font-medium">
                {{ __('messages.cosmetics.nav.products') }}
            </a>
            <a href="#best-sellers" @click="mobileMenuOpen = false" class="block py-2 text-cream-100 hover:text-gold-400 transition-colors duration-200 font-medium">
                {{ __('messages.cosmetics.nav.best_sellers') }}
            </a>
            <a href="#contact" @click="mobileMenuOpen = false" class="block py-2 text-cream-100 hover:text-gold-400 transition-colors duration-200 font-medium">
                {{ __('messages.cosmetics.nav.contact') }}
            </a>
            @guest
                <a href="{{ route('login') }}" class="block py-2 text-cream-100 hover:text-gold-400 transition-colors duration-200 font-medium">
                    {{ __('messages.store.login') }}
                </a>
            @endguest
        </div>
    </div>
</nav>
