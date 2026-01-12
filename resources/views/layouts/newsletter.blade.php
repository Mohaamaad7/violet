<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Flower Violet') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Logo -->
        <div class="mb-8">
            <a href="{{ url('/') }}">
                @php
                    $logo = \App\Models\Setting::get('site_logo');
                @endphp
                @if($logo)
                    <img src="{{ Storage::url($logo) }}" alt="{{ config('app.name') }}" class="h-16 w-auto">
                @else
                    <span class="text-2xl font-bold text-purple-600">{{ config('app.name', 'Flower Violet') }}</span>
                @endif
            </a>
        </div>

        <!-- Content -->
        @yield('content')
    </div>
</body>
</html>
