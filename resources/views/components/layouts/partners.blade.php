<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="h-full bg-gray-50 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? config('app.name') }} - {{ __('messages.partners.dashboard.title') }}</title>
    
    <!-- Cairo Font for Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    
    <!-- Filament Styles -->
    @filamentStyles
    @vite('resources/css/app.css')
    
    <style>
        body {
            font-family: 'Cairo', ui-sans-serif, system-ui, sans-serif;
        }
        
        /* Hide elements with x-cloak until Alpine is ready */
        [x-cloak] {
            display: none !important;
        }
        
        /* Hide scrollbar for sidebar */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Custom Purple colors matching project theme */
        :root {
            --primary-50: #f5f3ff;
            --primary-100: #ede9fe;
            --primary-500: #8b5cf6;
            --primary-600: #7c3aed;
            --primary-700: #6d28d9;
            --primary-900: #4c1d95;
        }
        
        /* Partners Content Area - Fixed margin for sidebar */
        @media (min-width: 1024px) {
            html[dir="rtl"] .partners-content-area {
                margin-right: 256px !important;
                margin-left: 0 !important;
            }
            
            html[dir="ltr"] .partners-content-area {
                margin-left: 256px !important;
                margin-right: 0 !important;
            }
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-200" x-data="{ sidebarOpen: false }">
    
    {{-- Sidebar --}}
    @include('components.layouts.partners.sidebar')
    
    {{-- Mobile Overlay - Click outside to close --}}
    <div x-show="sidebarOpen" 
         x-cloak
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40 lg:hidden">
    </div>
    
    {{-- Main Content Area - Positioned after sidebar on desktop --}}
    <div class="transition-all duration-300 ease-in-out relative z-10 partners-content-area">
        
        {{-- Top Header --}}
        @include('components.layouts.partners.topbar')
        
        {{-- Page Content (scrollable) --}}
        <main class="min-h-screen p-6 lg:p-8">
            {{ $slot }}
        </main>
        
    </div>
    
    <!-- Filament Scripts -->
    @filamentScripts
    @vite('resources/js/app.js')
    
    <!-- Initialize Phosphor Icons -->
    <script>
        // Initialize Phosphor Icons after DOM load
        document.addEventListener('DOMContentLoaded', function() {
            // No initialization needed for web version
        });
    </script>
</body>
</html>
