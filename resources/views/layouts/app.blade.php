<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Meta Tags -->
    <meta name="application-name" content="Gwache App">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Gwache App">
    <meta name="description" content="Fresh produce exchange and agriculture commerce platform">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-TileColor" content="#10b981">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="theme-color" content="#10b981">

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-180x180.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/icons/icon-167x167.png">

    <!-- Web App Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/icons/icon-32x32.png">
    <link rel="shortcut icon" href="/icons/icon-32x32.png">

    <title>{{ config('app.name', 'Gwache App') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Stack for additional styles -->
    @stack('styles')
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
        <header class="bg-white shadow hidden sm:block">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif

        <!-- Page Content -->
        <main class="pb-20 sm:pb-6"> <!-- Added consistent bottom padding for mobile nav -->
            {{ $slot }}
        </main>
    </div>

    <!-- Mobile Bottom Navigation - App-like -->
    <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-2 px-4 z-50 shadow-lg">
        <div class="grid grid-cols-4 gap-1">
            <a href="{{ route('dashboard') }}"
                class="flex flex-col items-center p-2 rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600' }}">
                <span class="text-2xl">🏠</span>
                <span class="text-xs mt-1 font-medium">Home</span>
            </a>
            <a href="{{ route('products.index') }}"
                class="flex flex-col items-center p-2 rounded-xl transition-colors {{ request()->routeIs('products.index') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600' }}">
                <span class="text-2xl">🔍</span>
                <span class="text-xs mt-1 font-medium">Browse</span>
            </a>
            <a href="{{ route('products.my') }}"
                class="flex flex-col items-center p-2 rounded-xl transition-colors {{ request()->routeIs('products.my') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600' }}">
                <span class="text-2xl">📦</span>
                <span class="text-xs mt-1 font-medium">My Items</span>
            </a>
            <a href="{{ route('exchanges.my') }}"
                class="flex flex-col items-center p-2 rounded-xl transition-colors {{ request()->routeIs('exchanges.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600' }}">
                <span class="text-2xl">🔄</span>
                <span class="text-xs mt-1 font-medium">Trades</span>
            </a>
        </div>
    </div>

    <!-- Stack for additional scripts -->
    @stack('scripts')

</body>

</html>