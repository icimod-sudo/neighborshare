<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Panel - {{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 min-h-screen">
            <div class="flex items-center justify-center h-16 bg-gray-900">
                <span class="text-white text-xl font-bold">Admin Panel</span>
            </div>
            <nav class="mt-8">
                <div class="px-4 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-gray-100 bg-gray-700 rounded-lg">
                        📊 Dashboard
                    </a>
                    <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-2 text-gray-300 hover:text-gray-100 hover:bg-gray-700 rounded-lg transition-colors">
                        👥 Users
                    </a>
                    <a href="{{ route('admin.products') }}" class="flex items-center px-4 py-2 text-gray-300 hover:text-gray-100 hover:bg-gray-700 rounded-lg transition-colors">
                        📦 Products
                    </a>
                    <a href="{{ route('admin.exchanges') }}" class="flex items-center px-4 py-2 text-gray-300 hover:text-gray-100 hover:bg-gray-700 rounded-lg transition-colors">
                        🔄 Exchanges
                    </a>
                    <a href="{{ route('admin.activity-logs') }}" class="flex items-center px-4 py-2 text-gray-300 hover:text-gray-100 hover:bg-gray-700 rounded-lg transition-colors">
                        📋 Activity Logs
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white shadow">
                <div class="flex justify-between items-center px-6 py-4">
                    <h1 class="text-2xl font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700">{{ Auth::user()->name }}</span>
                        <a href="{{ route('dashboard') }}" class="text-blue-500 hover:text-blue-700">User Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-gray-700">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>