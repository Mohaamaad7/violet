{{-- Store Header Component --}}
<header class="bg-white shadow-md sticky top-0 z-50">
    {{-- Top Bar --}}
    <div class="bg-gradient-to-r from-violet-600 to-violet-800 text-white py-2">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center text-sm">
                {{-- Contact Info --}}
                <div class="hidden md:flex items-center gap-4">
                    <a href="mailto:info@floweviolet.com"
                        class="hover:text-cream-200 transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        info@violet.com
                    </a>
                    <a href="tel:+201234567890" class="hover:text-cream-200 transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        +201091191056
                    </a>
                </div>

                {{-- Free Shipping Message --}}
                <div class="mx-auto md:mx-0 text-center md:text-left">
                    <span class="font-medium">🚚 {{ trans_db('store.header.free_shipping') }}</span>
                </div>

                {{-- Language Switcher --}}
                {{-- Language Switcher --}}
                <div class="hidden md:flex items-center">
                    <a href="{{ route('locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
                        class="flex items-center gap-2 px-3 py-1 rounded-full hover:bg-white/10 transition-colors duration-200"
                        aria-label="{{ app()->getLocale() === 'ar' ? 'Switch to English' : 'Switch to Arabic' }}">

                        @if(app()->getLocale() === 'ar')
                            {{-- Show UK Flag for English --}}
                            <img src="https://flagcdn.com/w40/gb.png" alt="English"
                                class="w-6 h-auto rounded-sm shadow-sm block">
                            <span class="font-bold text-xs tracking-wide pt-0.5 font-sans">English</span>
                        @else
                            {{-- Show Egypt Flag for Arabic --}}
                            <img src="https://flagcdn.com/w40/eg.png" alt="Arabic"
                                class="w-6 h-auto rounded-sm shadow-sm block">
                            <span class="font-bold text-xs tracking-wide pt-0.5 font-sans">العربية</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Header --}}
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between gap-4">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2 flex-shrink-0">
                @php
                    $logoPath = setting('logo_icon');
                @endphp
                @if($logoPath && $logoPath !== '')
                    <img src="{{ asset($logoPath) }}" alt="{{ config('app.name') }} Logo" class="w-16 h-16 object-contain">
                @else
                    {{-- Placeholder when no logo is uploaded --}}
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg flex items-center justify-center">
                        <span class="text-2xl text-gray-500">🌸</span>
                    </div>
                @endif
                <span
                    class="text-2xl font-bold bg-gradient-to-r from-violet-600 to-violet-800 bg-clip-text text-transparent hidden sm:block">
                    {{ config('app.name') }}
                </span>
            </a>

            {{-- Search Bar (Desktop) --}}
            <div class="hidden lg:flex flex-1 max-w-2xl">
                <livewire:store.search-bar />
            </div>

            {{-- Header Actions --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                {{-- Mobile Search Toggle --}}
                <button onclick="toggleMobileSearch()" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>

                {{-- User Account --}}
                <a href="/account"
                    class="hidden sm:flex items-center gap-2 px-3 py-2 hover:bg-gray-100 rounded-lg transition group">
                    <svg class="w-6 h-6 text-gray-700 group-hover:text-violet-600 transition" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="hidden md:block text-sm font-medium text-gray-700 group-hover:text-violet-600 transition">
                        @auth('customer')
                            {{ auth('customer')->user()->name }}
                        @else
                            {{ trans_db('store.header.account') }}
                        @endauth
                    </span>
                </a>

                {{-- Wishlist (Livewire Component) --}}
                <livewire:store.wishlist-counter />

                {{-- Cart --}}
                <button type="button" onclick="openCart()"
                    class="relative p-2 hover:bg-gray-100 rounded-lg transition group"
                    title="{{ trans_db('store.cart.shopping_cart') }}">
                    <svg class="w-6 h-6 text-gray-700 group-hover:text-violet-600 transition" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span id="cart-counter"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"
                        x-data="{ count: 0 }"
                        x-init="$nextTick(() => { let cm = Livewire.find('cart-manager'); if (cm) count = cm.cartCount || 0; })"
                        @cart-count-updated.window="count = $event.detail.count" x-show="count > 0" x-text="count"
                        x-cloak>
                    </span>
                </button>

                {{-- Mobile Menu Toggle --}}
                <button onclick="toggleMobileMenu()" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg id="mobile-menu-icon" class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="mobile-menu-close" class="w-6 h-6 text-gray-700 hidden" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Search Bar --}}
    <div id="mobile-search-bar" class="hidden lg:hidden border-t border-gray-200 px-4 py-3">
        <livewire:store.search-bar :is-mobile="true" />
    </div>

    {{-- Main Navigation (Desktop) --}}
    <nav class="hidden lg:block border-t border-gray-200 bg-cream-50">
        <div class="container mx-auto px-4">
            <ul class="flex items-center gap-8 py-3">
                <li>
                    <a href="/"
                        class="text-gray-700 hover:text-violet-600 font-medium transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        {{ trans_db('store.header.home') }}
                    </a>
                </li>
                <li>
                    <a href="/products" class="text-gray-700 hover:text-violet-600 font-medium transition">
                        {{ trans_db('store.header.products') }}
                    </a>
                </li>
                <li class="relative group">
                    <button class="text-gray-700 hover:text-violet-600 font-medium transition flex items-center gap-1">
                        {{ trans_db('store.header.categories') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    {{-- Hierarchical Dropdown Menu (WordPress Style) --}}
                    <div
                        class="absolute {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} top-full mt-2 w-64 bg-white shadow-xl rounded-lg py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <ul class="divide-y divide-gray-100">
                            @foreach(\App\Models\Category::with('children')->whereNull('parent_id')->where('is_active', true)->orderBy('order')->get() as $parentCategory)
                                <li class="relative group/submenu">
                                    <a href="{{ route('category.show', $parentCategory->slug) }}"
                                        class="flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-violet-50 hover:text-violet-600 transition-colors">
                                        <span class="flex items-center gap-2 font-medium">
                                            @if($parentCategory->icon)
                                                @if(Str::startsWith($parentCategory->icon, 'heroicon'))
                                                    @svg($parentCategory->icon, 'w-4 h-4')
                                                @else
                                                    <i class="{{ $parentCategory->icon }} text-sm"></i>
                                                @endif
                                            @else
                                                @svg('heroicon-o-tag', 'w-4 h-4')
                                            @endif
                                            {{ $parentCategory->name }}
                                        </span>
                                        @if($parentCategory->children->count() > 0)
                                            <svg class="w-4 h-4 {{ app()->getLocale() === 'ar' ? 'rotate-180' : '' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        @endif
                                    </a>

                                    {{-- Submenu for children categories --}}
                                    @if($parentCategory->children->count() > 0)
                                        <div
                                            class="absolute {{ app()->getLocale() === 'ar' ? 'right-full mr-1' : 'left-full ml-1' }} top-0 w-56 bg-white shadow-xl rounded-lg py-2 opacity-0 invisible group-hover/submenu:opacity-100 group-hover/submenu:visible transition-all duration-200">
                                            <ul class="divide-y divide-gray-100">
                                                @foreach($parentCategory->children as $childCategory)
                                                    <li>
                                                        <a href="{{ route('category.show', $childCategory->slug) }}"
                                                            class="block px-4 py-2.5 text-sm text-gray-600 hover:bg-violet-50 hover:text-violet-600 transition-colors">
                                                            <span class="flex items-center gap-2">
                                                                <svg class="w-3 h-3" fill="currentColor"
                                                                    viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd"
                                                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                                {{ $childCategory->name }}
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-2 pt-2 px-4 border-t border-gray-200">
                            <a href="/products"
                                class="block py-2 text-violet-600 hover:text-violet-700 font-semibold text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                {{ trans_db('store.header.view_all_products') }}
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <a href="/offers"
                        class="text-red-600 hover:text-red-700 font-bold transition flex items-center gap-1">
                        🔥 {{ trans_db('store.header.offers') }}
                    </a>
                </li>
                <li>
                    <a href="/about" class="text-gray-700 hover:text-violet-600 font-medium transition">
                        {{ trans_db('store.header.about') }}
                    </a>
                </li>
                <li>
                    <a href="/contact" class="text-gray-700 hover:text-violet-600 font-medium transition">
                        {{ trans_db('store.header.contact') }}
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    {{-- Mobile Navigation Menu --}}
    <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-200 bg-white">
        <nav class="container mx-auto px-4 py-4">
            <ul class="space-y-1">
                <li>
                    <a href="/" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        🏠 {{ trans_db('store.header.home') }}
                    </a>
                </li>
                <li>
                    <a href="/products" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        {{ trans_db('store.header.products') }}
                    </a>
                </li>
                
                {{-- Categories with nested structure --}}
                <li>
                    <button onclick="toggleMobileCategories()" 
                        class="w-full flex items-center justify-between py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        <span>{{ trans_db('store.header.categories') }}</span>
                        <svg id="mobile-categories-icon" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="mobile-categories-list" class="hidden {{ app()->getLocale() === 'ar' ? 'pr-4' : 'pl-4' }} mt-1 space-y-1">
                        @foreach(\App\Models\Category::with('children')->whereNull('parent_id')->where('is_active', true)->orderBy('order')->get() as $parentCategory)
                            <div class="border-{{ app()->getLocale() === 'ar' ? 'r' : 'l' }}-2 border-gray-200 {{ app()->getLocale() === 'ar' ? 'pr-3' : 'pl-3' }}">
                                <div class="flex items-center justify-between py-2">
                                    <a href="{{ route('category.show', $parentCategory->slug) }}" 
                                        class="flex-1 text-sm text-gray-600 hover:text-violet-600 font-medium flex items-center gap-2">
                                        @if($parentCategory->icon)
                                            @if(Str::startsWith($parentCategory->icon, 'heroicon'))
                                                @svg($parentCategory->icon, 'w-4 h-4')
                                            @else
                                                <i class="{{ $parentCategory->icon }} text-xs"></i>
                                            @endif
                                        @endif
                                        {{ $parentCategory->name }}
                                    </a>
                                    @if($parentCategory->children->count() > 0)
                                        <button onclick="toggleSubcategory({{ $parentCategory->id }})" 
                                            class="p-1 text-gray-400 hover:text-violet-600">
                                            <svg id="subcategory-icon-{{ $parentCategory->id }}" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                                
                                @if($parentCategory->children->count() > 0)
                                    <div id="subcategory-{{ $parentCategory->id }}" class="hidden {{ app()->getLocale() === 'ar' ? 'pr-3' : 'pl-3' }} space-y-1 pb-2">
                                        @foreach($parentCategory->children as $childCategory)
                                            <a href="{{ route('category.show', $childCategory->slug) }}" 
                                                class="block py-1.5 text-xs text-gray-500 hover:text-violet-600 flex items-center gap-2">
                                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $childCategory->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </li>
                
                <li>
                    <a href="/offers" class="block py-2 text-red-600 hover:text-red-700 font-bold transition">
                        🔥 {{ trans_db('store.header.offers') }}
                    </a>
                </li>
                <li>
                    <a href="/about" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        {{ trans_db('store.header.about') }}
                    </a>
                </li>
                <li>
                    <a href="/contact" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        {{ trans_db('store.header.contact') }}
                    </a>
                </li>
                <li class="pt-3 border-t border-gray-200">
                    <a href="/account" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        👤 {{ trans_db('store.header.my_account') }}
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>

<script>     /**      * Open Cart Slide-Over (Task 9.5 Cart Integration)      * Finds CartManager Livewire component and opens the slide-over panel      */
    window.openCart = function () {
        const components = window.Livewire.all();
        const cartManager = components.find(c => c.name === 'store.cart-manager');

        if (cartManager) {
            console.log('🎯 Opening Cart Slide-Over via CartManager component');
            cartManager.$wire.isOpen = true;
        } else {
            console.error('❌ CartManager component not found. Available components:',
                components.map(c => c.name)
            );
        }
    };
     /**      * Toggle Mobile Menu      */
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const icon = document.getElementById('mobile-menu-icon');
        const close = document.getElementById('mobile-menu-close');

        if (menu && icon && close) {
            menu.classList.toggle('hidden');
            icon.classList.toggle('hidden');
            close.classList.toggle('hidden');
        }
    }
     /**      * Toggle Mobile Search Bar      */
    function toggleMobileSearch() {
        const searchBar = document.getElementById('mobile-search-bar');
        if (searchBar) {
            searchBar.classList.toggle('hidden');
        }
    }

    /**
     * Toggle Mobile Categories List
     */
    function toggleMobileCategories() {
        const list = document.getElementById('mobile-categories-list');
        const icon = document.getElementById('mobile-categories-icon');
        
        if (list && icon) {
            list.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    }

    /**
     * Toggle Subcategory List
     */
    function toggleSubcategory(categoryId) {
        const subcategoryList = document.getElementById(`subcategory-${categoryId}`);
        const icon = document.getElementById(`subcategory-icon-${categoryId}`);
        
        if (subcategoryList && icon) {
            subcategoryList.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    }
</script>