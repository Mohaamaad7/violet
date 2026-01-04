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
    </style>
</head>
<body class="h-full overflow-hidden text-gray-800 dark:text-gray-200">
    
    <div class="flex h-screen" x-data="{ sidebarOpen: false }">
        
        @include('components.layouts.partners.sidebar')
        
        <!-- Overlay for mobile -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden backdrop-blur-sm"
             style="display: none;"></div>
        
        <!-- Main Content -->
        <div class="flex flex-col flex-1 overflow-hidden min-w-0">
            
            @include('components.layouts.partners.topbar')
            
            <!-- Page Content (scrollable) -->
            <main class="flex-1 overflow-y-auto p-6 lg:p-10 bg-gray-50 dark:bg-gray-950">
                {{ $slot }}
            </main>
        </div>
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
