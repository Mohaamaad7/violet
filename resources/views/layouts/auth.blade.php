<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('messages.login') }} - {{ config('app.name', 'Violet') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gradient-to-br from-violet-50 via-white to-violet-100 text-gray-900">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center gap-2" wire:navigate>
                        <div class="w-10 h-10 bg-violet-600 rounded-xl flex items-center justify-center">
                            <span class="text-white font-bold text-xl">V</span>
                        </div>
                        <span class="text-2xl font-bold text-violet-600">Violet</span>
                    </a>

                    <!-- Back to Store -->
                    <a href="{{ route('home') }}" 
                       class="text-sm text-gray-600 hover:text-violet-600 transition-colors flex items-center gap-2"
                       wire:navigate>
                        <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        {{ __('messages.continue_shopping') }}
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <!-- Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    {{ $slot }}
                </div>

                <!-- Footer Links -->
                <div class="mt-6 text-center text-sm text-gray-500">
                    {{ __('messages.all_rights_reserved') }} © {{ date('Y') }} Violet
                </div>
            </div>
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
