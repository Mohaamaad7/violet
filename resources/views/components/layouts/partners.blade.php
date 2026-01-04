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
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
    
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
        
        <!-- Sidebar (القائمة الجانبية) -->
        <aside id="sidebar" 
               class="fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} z-50 w-64 bg-white dark:bg-gray-900 border-{{ app()->getLocale() === 'ar' ? 'l' : 'r' }} border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto"
               :class="sidebarOpen ? 'translate-x-0' : '{{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}'">
            
            <!-- Logo -->
            <div class="flex items-center justify-center h-20 border-b border-gray-100 dark:border-gray-800 px-4">
                <div class="flex items-center gap-2 text-primary-700 dark:text-primary-400 font-bold text-xl">
                    <i class="ph ph-hexagon-fill text-3xl"></i>
                    <span>Flower Violet</span>
                </div>
            </div>
            
            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto no-scrollbar">
                
                <a href="{{ route('filament.partners.pages.influencer-dashboard') }}" 
                   class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('filament.partners.pages.influencer-dashboard') ? 'text-white bg-primary-600 dark:bg-primary-600 shadow-md shadow-primary-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-700 dark:hover:text-primary-400' }} rounded-xl transition-all group">
                    <i class="ph ph-squares-four text-xl"></i>
                    <span class="font-semibold">{{ __('messages.partners.nav.dashboard') }}</span>
                </a>
                
                <a href="{{ route('filament.partners.pages.profile-page') }}" 
                   class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('filament.partners.pages.profile-page') ? 'text-white bg-primary-600 dark:bg-primary-600 shadow-md shadow-primary-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-700 dark:hover:text-primary-400' }} rounded-xl transition-colors group">
                    <i class="ph ph-user text-xl group-hover:text-primary-600 dark:group-hover:text-primary-400"></i>
                    <span class="font-medium">{{ __('messages.partners.nav.profile') }}</span>
                </a>
                
                <a href="{{ route('filament.partners.pages.commissions-page') }}" 
                   class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('filament.partners.pages.commissions-page') ? 'text-white bg-primary-600 dark:bg-primary-600 shadow-md shadow-primary-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-700 dark:hover:text-primary-400' }} rounded-xl transition-colors group">
                    <i class="ph ph-chart-bar text-xl group-hover:text-primary-600 dark:group-hover:text-primary-400"></i>
                    <span class="font-medium">{{ __('messages.partners.nav.commissions') }}</span>
                </a>
                
                <a href="{{ route('filament.partners.pages.discount-codes-page') }}" 
                   class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('filament.partners.pages.discount-codes-page') ? 'text-white bg-primary-600 dark:bg-primary-600 shadow-md shadow-primary-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-700 dark:hover:text-primary-400' }} rounded-xl transition-colors group">
                    <i class="ph ph-ticket text-xl group-hover:text-primary-600 dark:group-hover:text-primary-400"></i>
                    <span class="font-medium">{{ __('messages.partners.nav.discount_codes') }}</span>
                </a>
                
                <a href="{{ route('filament.partners.pages.payouts-page') }}" 
                   class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('filament.partners.pages.payouts-page') ? 'text-white bg-primary-600 dark:bg-primary-600 shadow-md shadow-primary-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-700 dark:hover:text-primary-400' }} rounded-xl transition-colors group">
                    <i class="ph ph-bank text-xl group-hover:text-primary-600 dark:group-hover:text-primary-400"></i>
                    <span class="font-medium">{{ __('messages.partners.nav.payouts') }}</span>
                </a>
                
                <!-- Settings Section -->
                <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-800">
                    <p class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">
                        {{ __('messages.partners.nav.settings') }}
                    </p>
                    
                    <form method="POST" action="{{ route('filament.partners.auth.logout') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full flex items-center gap-3 px-4 py-3 text-gray-600 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-xl transition-colors group">
                            <i class="ph ph-sign-out text-xl group-hover:text-red-600 dark:group-hover:text-red-400"></i>
                            <span class="font-medium">{{ __('messages.partners.nav.logout') }}</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>
        
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
            
            <!-- Topbar (نفس تصميم الأدمن بالحرف) -->
            <div class="fi-topbar-ctn">
                <nav class="fi-topbar">
                    @if (true) {{-- hasNavigation --}}
                        <x-filament::icon-button
                            color="gray"
                            icon="heroicon-o-bars-3"
                            icon-size="lg"
                            label="Open sidebar"
                            x-cloak
                            x-data="{}"
                            x-on:click="sidebarOpen = true"
                            x-show="! sidebarOpen"
                            class="fi-topbar-open-sidebar-btn lg:hidden"
                        />

                        <x-filament::icon-button
                            color="gray"
                            icon="heroicon-o-x-mark"
                            icon-size="lg"
                            label="Close sidebar"
                            x-cloak
                            x-data="{}"
                            x-on:click="sidebarOpen = false"
                            x-show="sidebarOpen"
                            class="fi-topbar-close-sidebar-btn lg:hidden"
                        />
                    @endif

                    <div class="fi-topbar-start">
                        <h1 class="text-xl font-bold text-gray-800 dark:text-gray-200 hidden sm:block">
                            {{ $heading ?? __('messages.partners.dashboard.title') }}
                        </h1>
                    </div>

                    <div class="fi-topbar-end">
                        @if (filament()->auth()->check())
                            <x-filament-panels::user-menu />
                        @endif
                    </div>
                </nav>
            </div>
            
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
