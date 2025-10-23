<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="pb-20 sm:pb-6"> <!-- Added padding for mobile bottom nav -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Profile Section - Mobile Only -->
            <div class="sm:hidden mb-4 pt-4 px-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <!-- Profile Picture -->
                        <div class="flex items-center space-x-3">
                            <div class="hidden sm:block w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-lg">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">{{ auth()->user()->name }}</h1>
                                <p class="text-sm text-gray-600">Welcome to Gwache App</p>
                            </div>
                        </div>
                    </div>
                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="bg-green-100 p-3 rounded-2xl hover:bg-green-200 transition-colors">
                            <span class="text-green-600 text-xl">👤</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-1 z-50 border border-gray-200">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center space-x-2">
                                <span>👤</span>
                                <span>Edit Profile</span>
                            </a>
                            <!-- Logout Form -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center space-x-2">
                                    <span>🚪</span>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exchange Request Alert - Mobile Optimized -->
            @if($pendingExchangesCount > 0)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 mt-4 p-4 mb-4 mx-4 sm:mx-0 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 p-2 rounded-lg">
                        <span class="text-yellow-600 text-lg">🔔</span>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-semibold text-yellow-800">
                            {{ $pendingExchangesCount }} pending request{{ $pendingExchangesCount > 1 ? 's' : '' }}
                        </p>
                        <a href="{{ route('exchanges.my') }}" class="text-xs text-yellow-700 underline mt-1 block">
                            View requests →
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Stats Grid - Mobile (2 cols) vs Desktop -->
            <div class="grid grid-cols-2 sm:grid-cols-2 gap-3 sm:gap-6 mb-6 px-4 sm:px-0 mt-4">
                <!-- Available Products -->
                <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm font-medium text-gray-500">Available</p>
                            <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_available_products'] ?? 0 }}+</p>
                            <p class="text-xs text-green-600 font-semibold mt-1">Ready to trade</p>
                        </div>
                        <div class="bg-green-100 p-2 sm:p-3 rounded-xl">
                            <span class="text-green-600 text-lg sm:text-xl">📦</span>
                        </div>
                    </div>
                </div>

                <!-- Free Products -->
                <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm font-medium text-gray-500">Free Items</p>
                            <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">{{ $stats['free_products'] ?? 0 }}+</p>
                            <p class="text-xs text-purple-600 font-semibold mt-1">No cost</p>
                        </div>
                        <div class="bg-purple-100 p-2 sm:p-3 rounded-xl">
                            <span class="text-purple-600 text-lg sm:text-xl">🎁</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Browse Categories - Mobile Horizontal Scroll -->
            <div class="mb-6 px-4 sm:px-0">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Browse Categories</h3>
                    <a href="{{ route('products.index') }}" class="text-blue-600 text-sm font-semibold">View All</a>
                </div>

                <div class="flex space-x-3 overflow-x-auto pb-4 hide-scrollbar">
                    <!-- Plants -->
                    <a href="{{ route('products.index', ['category' => 'plants']) }}"
                        class="flex-shrink-0 w-24 bg-white rounded-2xl shadow-sm p-4 border border-gray-200 hover:border-emerald-200 hover:bg-emerald-50 transition-all active:scale-95">
                        <div class="text-center">
                            <span class="text-3xl mb-2 block">🌱</span>
                            <p class="font-semibold text-gray-900 text-sm">Plants</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $stats['category_counts']['plants'] ?? 0 }} items</p>
                        </div>
                    </a>

                    <!-- Vegetable -->
                    <a href="{{ route('products.index', ['category' => 'vegetable']) }}"
                        class="flex-shrink-0 w-24 bg-white rounded-2xl shadow-sm p-4 border border-gray-200 hover:border-green-200 hover:bg-green-50 transition-all active:scale-95">
                        <div class="text-center">
                            <span class="text-3xl mb-2 block">🥦</span>
                            <p class="font-semibold text-gray-900 text-sm">Vegetables</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $stats['category_counts']['vegetable'] ?? 0 }} items</p>
                        </div>
                    </a>

                    <!-- Fruit -->
                    <a href="{{ route('products.index', ['category' => 'fruit']) }}"
                        class="flex-shrink-0 w-24 bg-white rounded-2xl shadow-sm p-4 border border-gray-200 hover:border-red-200 hover:bg-red-50 transition-all active:scale-95">
                        <div class="text-center">
                            <span class="text-3xl mb-2 block">🍎</span>
                            <p class="font-semibold text-gray-900 text-sm">Fruits</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $stats['category_counts']['fruit'] ?? 0 }} items</p>
                        </div>
                    </a>

                    <!-- Dairy -->
                    <a href="{{ route('products.index', ['category' => 'dairy']) }}"
                        class="flex-shrink-0 w-24 bg-white rounded-2xl shadow-sm p-4 border border-gray-200 hover:border-blue-200 hover:bg-blue-50 transition-all active:scale-95">
                        <div class="text-center">
                            <span class="text-3xl mb-2 block">🥛</span>
                            <p class="font-semibold text-gray-900 text-sm">Dairy</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $stats['category_counts']['dairy'] ?? 0 }} items</p>
                        </div>
                    </a>

                    <!-- Free Items -->
                    <a href="{{ route('products.index', ['free_only' => true]) }}"
                        class="flex-shrink-0 w-24 bg-white rounded-2xl shadow-sm p-4 border border-gray-200 hover:border-purple-200 hover:bg-purple-50 transition-all active:scale-95">
                        <div class="text-center">
                            <span class="text-3xl mb-2 block">🎁</span>
                            <p class="font-semibold text-gray-900 text-sm">Free</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $stats['free_products'] ?? 0 }} items</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Map Nearby Products Section -->
            <div class="mb-6 px-4 sm:px-0">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Map Nearby Products</h3>
                    <a href="{{ route('products.map') }}" class="text-blue-600 text-sm font-semibold">View Full Map</a>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-200">
                    <!-- Mini Map Container -->
                    <div class="relative bg-gray-100 rounded-xl h-48 sm:h-56 flex items-center justify-center overflow-hidden">
                        <!-- Map Placeholder with Interactive Elements -->
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-green-50">
                            <!-- Map Grid -->
                            <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(#e5e7eb 1px, transparent 1px), linear-gradient(90deg, #e5e7eb 1px, transparent 1px); background-size: 20px 20px;"></div>

                            <!-- Location Marker -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                <div class="relative">
                                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                                        <span class="text-white text-sm">📍</span>
                                    </div>
                                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                                </div>
                            </div>

                            <!-- Nearby Product Markers -->
                            <div class="absolute top-1/3 left-1/4">
                                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-md">
                                    <span class="text-white text-xs">🥦</span>
                                </div>
                            </div>
                            <div class="absolute top-2/3 right-1/3">
                                <div class="w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center shadow-md">
                                    <span class="text-white text-xs">🍎</span>
                                </div>
                            </div>
                            <div class="absolute bottom-1/4 right-1/4">
                                <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center shadow-md">
                                    <span class="text-white text-xs">🥛</span>
                                </div>
                            </div>
                            <!-- Plants Marker -->
                            <div class="absolute top-1/4 right-1/4">
                                <div class="w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center shadow-md">
                                    <span class="text-white text-xs">🌱</span>
                                </div>
                            </div>
                        </div>

                        <!-- Map Overlay Content -->
                        <div class="relative z-10 text-center p-4">
                            <div class="bg-white bg-opacity-90 rounded-xl p-4 shadow-sm inline-block">
                                <h4 class="font-bold text-gray-900 text-sm sm:text-base">Discover Local Produce</h4>
                                <p class="text-xs text-gray-600 mt-1">Find fresh items near your location</p>
                                <a href="{{ route('products.map') }}"
                                    class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center space-x-2 transition-colors">
                                    <span>🗺️</span>
                                    <span>Open Map</span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Quick Actions Grid - Mobile Optimized -->
            <div class="mb-6 px-4 sm:px-0">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                    <!-- Add Product -->
                    <a href="{{ route('products.create') }}"
                        class="bg-white rounded-2xl shadow-sm p-4 border border-gray-200 hover:border-green-200 hover:bg-green-50 transition-all active:scale-95 group">
                        <div class="text-center">
                            <div class="bg-green-100 w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-green-200 transition-colors">
                                <span class="text-green-600 text-xl">➕</span>
                            </div>
                            <p class="font-semibold text-gray-900 text-sm mb-1">Add Product</p>
                            <p class="text-xs text-gray-500">List item</p>
                        </div>
                    </a>

                    <!-- My Products -->
                    <a href="{{ route('products.my') }}"
                        class="bg-white rounded-2xl shadow-sm p-4 border border-gray-200 hover:border-blue-200 hover:bg-blue-50 transition-all active:scale-95 group">
                        <div class="text-center">
                            <div class="bg-blue-100 w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 transition-colors">
                                <span class="text-blue-600 text-xl">📦</span>
                            </div>
                            <p class="font-semibold text-gray-900 text-sm mb-1">My Products</p>
                            <p class="text-xs text-gray-500">Manage</p>
                        </div>
                    </a>

                    <!-- Browse Products -->
                    <a href="{{ route('products.index') }}"
                        class="bg-white rounded-2xl shadow-sm p-4 border border-gray-200 hover:border-orange-200 hover:bg-orange-50 transition-all active:scale-95 group">
                        <div class="text-center">
                            <div class="bg-orange-100 w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-orange-200 transition-colors">
                                <span class="text-orange-600 text-xl">🔍</span>
                            </div>
                            <p class="font-semibold text-gray-900 text-sm mb-1">Browse</p>
                            <p class="text-xs text-gray-500">Discover</p>
                        </div>
                    </a>

                    <!-- Map View - Hidden on smallest screens -->
                    <a href="{{ route('products.map') }}"
                        class="hidden sm:block bg-white rounded-2xl shadow-sm p-4 border border-gray-200 hover:border-red-200 hover:bg-red-50 transition-all active:scale-95 group">
                        <div class="text-center">
                            <div class="bg-red-100 w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-red-200 transition-colors">
                                <span class="text-red-600 text-xl">🗺️</span>
                            </div>
                            <p class="font-semibold text-gray-900 text-sm mb-1">Map View</p>
                            <p class="text-xs text-gray-500">Nearby</p>
                        </div>
                    </a>

                    <!-- My Exchanges -->
                    <a href="{{ route('exchanges.my') }}"
                        class="bg-white rounded-2xl shadow-sm p-4 border border-gray-200 hover:border-purple-200 hover:bg-purple-50 transition-all active:scale-95 group">
                        <div class="text-center">
                            <div class="bg-purple-100 w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-purple-200 transition-colors">
                                <span class="text-purple-600 text-xl">🔄</span>
                            </div>
                            <p class="font-semibold text-gray-900 text-sm mb-1">Exchanges</p>
                            <p class="text-xs text-gray-500">Requests</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Browse All Products Button - Mobile Sticky -->
            <div class="sm:hidden fixed bottom-20 left-4 right-4 z-40">
                <a href="{{ route('products.index') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-2xl py-4 px-6 shadow-lg flex items-center justify-center space-x-3 transition-all active:scale-95">
                    <span class="text-xl">🔍</span>
                    <span class="font-bold text-lg">Browse All Products</span>
                </a>
            </div>

        </div>
    </div>

    <!-- Mobile Bottom Navigation - App-like -->
    <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 py-3 px-6 z-50 shadow-2xl">
        <div class="grid grid-cols-4 gap-2">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center p-2 text-blue-600">
                <span class="text-2xl">🏠</span>
                <span class="text-xs mt-1 font-bold">Home</span>
            </a>
            <a href="{{ route('products.index') }}" class="flex flex-col items-center p-2 text-gray-600 hover:text-blue-600 transition-colors">
                <span class="text-2xl">🔍</span>
                <span class="text-xs mt-1">Browse</span>
            </a>
            <a href="{{ route('products.my') }}" class="flex flex-col items-center p-2 text-gray-600 hover:text-blue-600 transition-colors">
                <span class="text-2xl">📦</span>
                <span class="text-xs mt-1">My Items</span>
            </a>
            <a href="{{ route('exchanges.my') }}" class="flex flex-col items-center p-2 text-gray-600 hover:text-blue-600 transition-colors">
                <span class="text-2xl">🔄</span>
                <span class="text-xs mt-1">Trades</span>
            </a>
        </div>
    </div>

    <style>
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>

    <!-- AlpineJS for dropdown functionality -->
    <script src="//unpkg.com/alpinejs" defer></script>
</x-app-layout>