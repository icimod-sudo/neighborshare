<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>NeighborShare - Share with Your Community</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="antialiased">
        <div class="relative min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
            <!-- Navigation -->
            <nav class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 flex items-center">
                                <span class="text-2xl font-bold text-green-600">🤝 NeighborShare</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900">Log in</a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Register</a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <div class="relative py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
                        Share More, 
                        <span class="text-green-600">Waste Less</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                        Connect with your neighbors to share FMCG products, fresh vegetables, and more. 
                        Build community while reducing waste.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @auth
                            <a href="{{ route('products.index') }}" 
                               class="bg-green-500 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-green-600 transition-colors">
                                Browse Products
                            </a>
                        @else
                            <a href="{{ route('register') }}" 
                               class="bg-green-500 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-green-600 transition-colors">
                                Join Your Community
                            </a>
                            <a href="{{ route('products.index') }}" 
                               class="border border-green-500 text-green-500 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-green-50 transition-colors">
                                Browse as Guest
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="py-16 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">How It Works</h2>
                        <p class="text-gray-600 max-w-2xl mx-auto">Simple steps to start sharing with your community</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="text-center">
                            <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">📝</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">1. List Products</h3>
                            <p class="text-gray-600">Share your extra FMCG products, vegetables, or other items you don't need</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">🔍</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">2. Discover Nearby</h3>
                            <p class="text-gray-600">Find products shared by people in your neighborhood</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">🤝</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">3. Connect & Share</h3>
                            <p class="text-gray-600">Arrange pickup and build connections with your community</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>