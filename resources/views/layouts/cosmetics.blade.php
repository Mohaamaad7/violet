<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Violet') }} - {{ __('messages.cosmetics.tagline') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <!-- Additional Styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Playfair Display for headings */
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
        
        /* Inter for body text */
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Glass effect */
        .glass-nav {
            background: rgba(59, 7, 100, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        /* Float animation */
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* RTL Support */
        [dir="rtl"] .rtl\:space-x-reverse > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen bg-violet-950 text-cream-100 antialiased">
    <!-- Navbar -->
    <x-cosmetics.navbar />

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <x-cosmetics.footer />

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed top-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }} z-50 space-y-2"></div>

    <!-- Cart Manager (Shared with Store) -->
    <livewire:store.cart-manager />

    @livewireScripts

    <!-- Toast Notification System -->
    <script>
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const bgColor = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-gold-400');
            const textColor = type === 'info' ? 'text-violet-950' : 'text-white';
            const icon = type === 'success' 
                ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
                : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            
            toast.className = `${bgColor} ${textColor} px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-full opacity-0`;
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
            let data = event.detail;
            if (Array.isArray(data) && data.length > 0) {
                data = data[0];
            }
            const message = data?.message || 'Success';
            const type = data?.type || 'success';
            window.showToast(message, type);
        });
    </script>

    @stack('scripts')
</body>
</html>
