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
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|playfair-display:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Custom Styles for Filters --}}
    <style>
        /* Alpine.js Cloak - Hide elements until Alpine loads */
        [x-cloak] {
            display: none !important;
        }

        /* Custom Scrollbar */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #c4b5fd;
            border-radius: 10px;
        }
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #a78bfa;
        }

        /* Checkbox Animation */
        input[type="checkbox"]:checked {
            animation: checkBounce 0.3s ease;
        }
        @keyframes checkBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Radio Button Animation */
        input[type="radio"]:checked {
            animation: radioPulse 0.3s ease;
        }
        @keyframes radioPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Star Hover Animation */
        .fa-star:hover {
            animation: starShine 0.5s ease;
        }
        @keyframes starShine {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2) rotate(10deg); }
        }

        /* Z-index hierarchy (CRITICAL - DO NOT CHANGE):
         * Header: z-50 (highest - always visible)
         * Sidebar: z-40 (below header, above content)
         * Quick View: z-30 (below sidebar)
         * Product Cards: z-10 (lowest)
         */
        
        /* Ensure sidebar stays below header */
        aside[style*="z-index: 40"] {
            position: sticky !important;
            z-index: 40 !important;
        }
        
        /* Product grid and cards */
        .product-grid-container {
            position: relative;
            z-index: 10;
        }
        
        .product-card {
            position: relative;
            z-index: 10;
        }
        
        /* Quick View overlay - must be below sidebar (z-40) */
        .product-card .group-hover\:opacity-100 {
            z-index: 30 !important;
        }

        /* Prevent body scroll when mobile filter panel is open */
        body.filter-open {
            overflow: hidden;
        }

        /* Mobile off-canvas panel smooth scrolling */
        .mobile-filter-panel {
            -webkit-overflow-scrolling: touch;
        }
    </style>

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

        {{-- Cart Manager (Slide-over) - CRITICAL: Must be included for add-to-cart to work --}}
        <livewire:store.cart-manager />
    </div>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Alpine.js will be started by Livewire automatically --}}
    <script>
        document.addEventListener('livewire:init', () => {
            console.log('✅ Livewire initialized');
            
            // Debug: Check if CartManager component exists in DOM
            setTimeout(() => {
                const cartManager = document.querySelector('[wire\\:id]');
                if (cartManager) {
                    console.log('✅ CartManager component found in DOM:', cartManager.getAttribute('wire:id'));
                } else {
                    console.error('❌ CartManager component NOT FOUND in DOM!');
                }
            }, 500);

            // CRITICAL: Listen for browser 'open-cart' event and forward to Livewire
            window.addEventListener('open-cart', () => {
                console.log('🎯 Browser event received, dispatching to Livewire...');
                Livewire.dispatch('open-cart');
            });
        });

        // Toast Notification System
        window.addEventListener('show-toast', (event) => {
            const detail = event.detail[0] || event.detail;
            const message = detail.message || 'تمت العملية بنجاح';
            const type = detail.type || 'success';
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `fixed top-20 right-4 z-[9999] px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3 animate-slide-in ${
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'error' ? 'bg-red-500 text-white' : 
                'bg-blue-500 text-white'
            }`;
            
            // Add icon
            const icon = type === 'success' 
                ? '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                : '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
            
            toast.innerHTML = `${icon}<span class="font-semibold">${message}</span>`;
            document.body.appendChild(toast);
            
            // Auto-remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('animate-slide-out');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        });
    </script>

    {{-- Toast Animations --}}
    <style>
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slide-out {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .animate-slide-in {
            animation: slide-in 0.3s ease-out forwards;
        }
        
        .animate-slide-out {
            animation: slide-out 0.3s ease-in forwards;
        }
    </style>

    {{-- Additional Scripts --}}
    @stack('scripts')
</body>
</html>
