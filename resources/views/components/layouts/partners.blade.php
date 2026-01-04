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
            
            <!-- Topbar (نفس تصميم Admin) -->
            <div class="fi-topbar-ctn">
                <nav class="fi-topbar">
                    <!-- Mobile Menu Buttons -->
                    <x-filament::icon-button
                        color="gray"
                        icon="heroicon-o-bars-3"
                        icon-size="lg"
                        label="فتح القائمة"
                        x-cloak
                        x-on:click="sidebarOpen = true"
                        x-show="! sidebarOpen"
                        class="fi-topbar-open-sidebar-btn lg:hidden"
                    />

                    <x-filament::icon-button
                        color="gray"
                        icon="heroicon-o-x-mark"
                        icon-size="lg"
                        label="إغلاق القائمة"
                        x-cloak
                        x-on:click="sidebarOpen = false"
                        x-show="sidebarOpen"
                        class="fi-topbar-close-sidebar-btn lg:hidden"
                    />

                    <!-- Topbar Start (Heading) -->
                    <div class="fi-topbar-start">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $heading ?? __('messages.partners.dashboard.title') }}
                        </h1>
                    </div>

                    <!-- Topbar End (User Menu) -->
                    <div class="fi-topbar-end">
                        <x-filament::dropdown
                            placement="bottom-end"
                            width="xs"
                            teleport
                        >
                            <x-slot name="trigger">
                                <button
                                    type="button"
                                    class="shrink-0"
                                    aria-label="قائمة المستخدم"
                                >
                                    <x-filament::avatar
                                        :src="filament()->getUserAvatarUrl(auth()->user())"
                                        :alt="filament()->getUserName(auth()->user())"
                                        size="md"
                                    />
                                </button>
                            </x-slot>

                            {{-- User Info Header --}}
                            <x-filament::dropdown.header class="!p-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ filament()->getUserName(auth()->user()) }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ auth()->user()->email }}
                                    </span>
                                </div>
                            </x-filament::dropdown.header>

                            {{-- Menu Items --}}
                            <x-filament::dropdown.list>
                                <x-filament::dropdown.list.item
                                    :href="route('filament.partners.pages.profile-page')"
                                    tag="a"
                                    icon="heroicon-o-user"
                                >
                                    {{ __('messages.partners.nav.profile') }}
                                </x-filament::dropdown.list.item>

                                <x-filament::dropdown.list.item
                                    tag="form"
                                    :action="route('filament.partners.auth.logout')"
                                    method="post"
                                    icon="heroicon-o-arrow-right-on-rectangle"
                                    color="danger"
                                >
                                    {{ __('messages.partners.nav.logout') }}
                                </x-filament::dropdown.list.item>
                            </x-filament::dropdown.list>
                        </x-filament::dropdown>
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
