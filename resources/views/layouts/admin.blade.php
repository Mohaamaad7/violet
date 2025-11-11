<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Violet') }} - لوحة التحكم</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen">
            <!-- Sidebar -->
            <aside class="fixed inset-y-0 right-0 z-50 w-64 bg-gradient-to-b from-purple-900 to-indigo-900 text-white shadow-xl transform transition-transform duration-300">
                <div class="flex items-center justify-center h-16 bg-purple-950">
                    <h1 class="text-2xl font-bold text-white">🌸 Violet</h1>
                </div>

                <nav class="mt-6 px-4 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('admin.dashboard') ? 'bg-white/20' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="font-medium">لوحة التحكم</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('admin.categories.*') ? 'bg-white/20' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <span class="font-medium">الفئات</span>
                    </a>

                    <a href="{{ route('admin.products.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('admin.products.*') ? 'bg-white/20' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="font-medium">المنتجات</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="flex items-center px-4 py-3 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('admin.orders.*') ? 'bg-white/20' : '' }}">
                        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span class="font-medium">الطلبات</span>
                    </a>

                    <div class="pt-6 mt-6 border-t border-white/10">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-4 py-3 rounded-lg hover:bg-red-500/20 transition text-red-200">
                                <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span class="font-medium">تسجيل الخروج</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="mr-64">
                <!-- Top Navbar -->
                <header class="bg-white shadow-sm sticky top-0 z-40">
                    <div class="flex items-center justify-between px-6 py-4">
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ $title ?? 'لوحة التحكم' }}
                        </h2>

                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-600">مرحباً، <strong>{{ auth()->user()->name }}</strong></span>
                            <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
        
        <script>
            // Auto-hide success messages after 3 seconds
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    const alerts = document.querySelectorAll('[role="alert"]');
                    alerts.forEach(alert => {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    });
                }, 3000);
            });
        </script>
    </body>
</html>
