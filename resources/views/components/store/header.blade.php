{{-- Store Header Component --}}
<header class="bg-white shadow-md sticky top-0 z-50">
    {{-- Top Bar --}}
    <div class="bg-gradient-to-r from-violet-600 to-violet-800 text-white py-2">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center text-sm">
                {{-- Contact Info --}}
                <div class="hidden md:flex items-center gap-4">
                    <a href="mailto:info@violet.com" class="hover:text-cream-200 transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        info@violet.com
                    </a>
                    <a href="tel:+201234567890" class="hover:text-cream-200 transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        +20 123 456 7890
                    </a>
                </div>
                
                {{-- Free Shipping Message --}}
                <div class="mx-auto md:mx-0 text-center md:text-left">
                    <span class="font-medium">🚚 Free shipping on orders over $50</span>
                </div>
                
                {{-- Language Switcher (Optional - can be removed if not needed) --}}
                <div class="hidden md:flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                    </svg>
                    <span>EN</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Header --}}
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between gap-4">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2 flex-shrink-0">
                <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-violet-700 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-violet-600 to-violet-800 bg-clip-text text-transparent hidden sm:block">
                    Violet
                </span>
            </a>

            {{-- Search Bar (Desktop) --}}
            <div class="hidden lg:flex flex-1 max-w-2xl">
                <div class="relative w-full">
                    <input 
                        type="text" 
                        placeholder="Search for products..."
                        class="w-full px-4 py-2.5 pl-10 pr-4 border-2 border-gray-200 rounded-lg focus:border-violet-500 focus:ring-2 focus:ring-violet-200 focus:outline-none transition"
                    >
                    <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- Header Actions --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                {{-- Mobile Search Toggle --}}
                <button onclick="toggleMobileSearch()" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>

                {{-- User Account --}}
                <a href="/account" class="hidden sm:flex items-center gap-2 px-3 py-2 hover:bg-gray-100 rounded-lg transition group">
                    <svg class="w-6 h-6 text-gray-700 group-hover:text-violet-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="hidden md:block text-sm font-medium text-gray-700 group-hover:text-violet-600 transition">Account</span>
                </a>

                {{-- Wishlist --}}
                <a href="/wishlist" class="relative p-2 hover:bg-gray-100 rounded-lg transition group">
                    <svg class="w-6 h-6 text-gray-700 group-hover:text-violet-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span id="wishlist-counter" class="absolute -top-1 -right-1 bg-violet-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">
                        0
                    </span>
                </a>

                {{-- Cart --}}
                <a href="/cart" class="relative p-2 hover:bg-gray-100 rounded-lg transition group">
                    <svg class="w-6 h-6 text-gray-700 group-hover:text-violet-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span id="cart-counter" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">
                        0
                    </span>
                </a>

                {{-- Mobile Menu Toggle --}}
                <button onclick="toggleMobileMenu()" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg id="mobile-menu-icon" class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="mobile-menu-close" class="w-6 h-6 text-gray-700 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Search Bar --}}
    <div id="mobile-search-bar" class="hidden lg:hidden border-t border-gray-200 px-4 py-3">
        <div class="relative">
            <input 
                type="text" 
                placeholder="Search for products..."
                class="w-full px-4 py-2.5 pl-10 pr-4 border-2 border-gray-200 rounded-lg focus:border-violet-500 focus:ring-2 focus:ring-violet-200 focus:outline-none"
            >
            <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </div>

    {{-- Main Navigation (Desktop) --}}
    <nav class="hidden lg:block border-t border-gray-200 bg-cream-50">
        <div class="container mx-auto px-4">
            <ul class="flex items-center gap-8 py-3">
                <li>
                    <a href="/" class="text-gray-700 hover:text-violet-600 font-medium transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Home
                    </a>
                </li>
                <li>
                    <a href="/products" class="text-gray-700 hover:text-violet-600 font-medium transition">
                        Products
                    </a>
                </li>
                <li class="relative group">
                    <button class="text-gray-700 hover:text-violet-600 font-medium transition flex items-center gap-1">
                        Categories
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    {{-- Dynamic Mega Menu with ALL Categories --}}
                    <div class="absolute left-0 top-full mt-2 w-screen max-w-6xl bg-white shadow-xl rounded-lg p-6 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                            @foreach(\App\Models\Category::with('children')->whereNull('parent_id')->where('is_active', true)->orderBy('order')->get() as $parentCategory)
                            <div>
                                <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                                    @if($parentCategory->icon)
                                    <i class="{{ $parentCategory->icon }} text-violet-600"></i>
                                    @else
                                    <i class="fas fa-tag text-violet-600"></i>
                                    @endif
                                    {{ $parentCategory->name }}
                                </h4>
                                @if($parentCategory->children->count() > 0)
                                <ul class="space-y-2 text-sm text-gray-600">
                                    @foreach($parentCategory->children->take(5) as $childCategory)
                                    <li>
                                        <a href="{{ route('category.show', $childCategory->slug) }}" class="hover:text-violet-600 transition flex items-center gap-1">
                                            <i class="fas fa-chevron-right text-[8px]"></i>
                                            {{ $childCategory->name }}
                                        </a>
                                    </li>
                                    @endforeach
                                    @if($parentCategory->children->count() > 5)
                                    <li>
                                        <a href="{{ route('category.show', $parentCategory->slug) }}" class="text-violet-600 hover:text-violet-700 font-semibold text-xs">
                                            View All ({{ $parentCategory->children->count() }})
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                                @else
                                <a href="{{ route('category.show', $parentCategory->slug) }}" class="text-sm text-violet-600 hover:text-violet-700">
                                    View Products
                                </a>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <a href="/products" class="text-violet-600 hover:text-violet-700 font-semibold flex items-center gap-2">
                                <i class="fas fa-th"></i>
                                View All Products
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <a href="/offers" class="text-red-600 hover:text-red-700 font-bold transition flex items-center gap-1">
                        🔥 Offers
                    </a>
                </li>
                <li>
                    <a href="/about" class="text-gray-700 hover:text-violet-600 font-medium transition">
                        About Us
                    </a>
                </li>
                <li>
                    <a href="/contact" class="text-gray-700 hover:text-violet-600 font-medium transition">
                        Contact Us
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    {{-- Mobile Navigation Menu --}}
    <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-200 bg-white">
        <nav class="container mx-auto px-4 py-4">
            <ul class="space-y-3">
                <li>
                    <a href="/" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        🏠 Home
                    </a>
                </li>
                <li>
                    <a href="/products" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        Products
                    </a>
                </li>
                <li>
                    <a href="/categories" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        Categories
                    </a>
                </li>
                <li>
                    <a href="/offers" class="block py-2 text-red-600 hover:text-red-700 font-bold transition">
                        🔥 Offers
                    </a>
                </li>
                <li>
                    <a href="/about" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        About Us
                    </a>
                </li>
                <li>
                    <a href="/contact" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        Contact Us
                    </a>
                </li>
                <li class="pt-3 border-t border-gray-200">
                    <a href="/account" class="block py-2 text-gray-700 hover:text-violet-600 font-medium transition">
                        👤 My Account
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>
