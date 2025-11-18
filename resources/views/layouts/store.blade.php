<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags --}}
    <title>{{ $title ?? config('app.name', 'Violet Store') }}</title>
    <meta name="description" content="{{ $description ?? 'Your premium e-commerce destination for quality products' }}">
    <meta name="keywords" content="{{ $keywords ?? 'e-commerce, online shop, violet store' }}">
    <meta name="author" content="Violet Store">
    
    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    <meta property="og:description" content="{{ $description ?? 'Your premium e-commerce destination' }}">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    {{-- Twitter --}}
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $title ?? config('app.name') }}">
    <meta property="twitter:description" content="{{ $description ?? 'Your premium e-commerce destination' }}">
    <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Additional Head Content --}}
    @stack('styles')
</head>
<body class="font-sans antialiased bg-cream-50 text-gray-900">
    {{-- Page Wrapper --}}
    <div class="min-h-screen flex flex-col">
        {{-- Header --}}
        <x-store.header />

        {{-- Main Content --}}
        <main class="flex-grow">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <x-store.footer />
    </div>

    {{-- Toast Notifications (if needed) --}}
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Additional Scripts --}}
    @stack('scripts')

    {{-- Alpine.js & Custom Scripts --}}
    <script>
        // Cart counter update function
        window.updateCartCounter = function(count) {
            const counter = document.getElementById('cart-counter');
            if (counter) {
                counter.textContent = count;
                counter.classList.toggle('hidden', count === 0);
            }
        };

        // Wishlist counter update function
        window.updateWishlistCounter = function(count) {
            const counter = document.getElementById('wishlist-counter');
            if (counter) {
                counter.textContent = count;
                counter.classList.toggle('hidden', count === 0);
            }
        };

        // Mobile menu toggle
        window.toggleMobileMenu = function() {
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');
            const closeIcon = document.getElementById('mobile-menu-close');
            
            if (mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.remove('hidden');
                menuIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            } else {
                mobileMenu.classList.add('hidden');
                menuIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        };

        // Search toggle (mobile)
        window.toggleMobileSearch = function() {
            const searchBar = document.getElementById('mobile-search-bar');
            searchBar.classList.toggle('hidden');
        };
    </script>
</body>
</html>
