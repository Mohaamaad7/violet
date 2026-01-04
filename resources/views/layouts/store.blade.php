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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Changa:wght@300;400;500;600;700;800&family=Figtree:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Custom Typography Styles --}}
    <style>
        /* Base Typography */
        body {
            font-family: 'Figtree', sans-serif;
        }

        /* Arabic Typography Override */
        [dir="rtl"] body {
            font-family: 'Cairo', sans-serif;
        }

        [dir="rtl"] h1,
        [dir="rtl"] h2,
        [dir="rtl"] h3,
        [dir="rtl"] h4,
        [dir="rtl"] h5,
        [dir="rtl"] h6,
        [dir="rtl"] .font-heading {
            font-family: 'Changa', sans-serif;
        }

        /* Section Header Styling */
        .section-header {
            position: relative;
            display: inline-block;
        }

        .section-header::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #7c3aed, #a855f7);
            /* Violet gradient */
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        [dir="rtl"] .section-header::after {
            left: auto;
            right: 0;
        }

        .group:hover .section-header::after {
            width: 100%;
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Facebook Pixel --}}
    <x-analytics.facebook-pixel :pixelId="setting('facebook_pixel_id')" />

    {{-- Additional Head Content --}}
    @stack('styles')
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900 selection:bg-violet-100 selection:text-violet-700">
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

    {{-- Cart Manager (Slide-over) - CRITICAL: Must be present on ALL pages --}}
    <livewire:store.cart-manager />

    {{-- Toast Notifications (if needed) --}}
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Additional Scripts --}}
    @stack('scripts')

    {{-- Alpine.js & Custom Scripts --}}
    <script>
        // Toast Notification System
        window.showToast = function (message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');

            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success'
                ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
                : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

            toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-full opacity-0`;
            toast.innerHTML = `
                ${icon}
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-2 hover:bg-white/20 rounded p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 100);

            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        };

        // Listen for show-toast event from Livewire
        window.addEventListener('show-toast', (event) => {
            // Handle both Livewire 3 formats: event.detail or event.detail[0]
            let data = event.detail;

            // Livewire 3 wraps named parameters in array
            if (Array.isArray(data) && data.length > 0) {
                data = data[0];
            }

            const message = data?.message || 'تم بنجاح';
            const type = data?.type || 'success';

            window.showToast(message, type);
        });

        // Cart counter update function
        window.updateCartCounter = function (count) {
            const counter = document.getElementById('cart-counter');
            if (counter) {
                counter.textContent = count;
                counter.classList.toggle('hidden', count === 0);
            }
        };

        // Wishlist counter update function
        window.updateWishlistCounter = function (count) {
            const counter = document.getElementById('wishlist-counter');
            if (counter) {
                counter.textContent = count;
                counter.classList.toggle('hidden', count === 0);
            }
        };

        // Mobile menu toggle
        window.toggleMobileMenu = function () {
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
        window.toggleMobileSearch = function () {
            const searchBar = document.getElementById('mobile-search-bar');
            searchBar.classList.toggle('hidden');
        };
    </script>

    {{-- Additional Scripts --}}
    @stack('scripts')
</body>

</html>