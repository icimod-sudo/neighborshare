<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Available Products') }}
            @if($userLatitude && $userLongitude)
            <span class="text-sm font-normal text-green-600 ml-2">📍 Showing products within {{ request('radius', 2) }}km radius</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Location Alert -->
            @if(!$userLatitude || !$userLongitude)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-yellow-400 text-xl">📍</span>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Location Not Set
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>
                                To see products near you, please update your location in your
                                <a href="{{ route('profile.edit') }}" class="underline font-medium">profile</a>.
                                Currently showing all available products.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Simple Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Category Filter -->
                        <div>
                            <select onchange="window.location.href=this.value"
                                class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="{{ route('products.index') }}">All Categories</option>
                                <option value="{{ route('products.category', 'vegetable') }}"
                                    {{ request()->is('*category/vegetable') ? 'selected' : '' }}>
                                    Vegetables
                                </option>
                                <option value="{{ route('products.category', 'fruit') }}"
                                    {{ request()->is('*category/fruit') ? 'selected' : '' }}>
                                    Fruits
                                </option>
                                <option value="{{ route('products.category', 'fmcg') }}"
                                    {{ request()->is('*category/fmcg') ? 'selected' : '' }}>
                                    FMCG
                                </option>
                                <option value="{{ route('products.category', 'dairy') }}"
                                    {{ request()->is('*category/dairy') ? 'selected' : '' }}>
                                    Dairy
                                </option>
                            </select>
                        </div>

                        <!-- Map View Button -->
                        <div>
                            <a href="{{ route('products.map') }}"
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center">
                                <span class="mr-2">🗺️</span> Map View
                            </a>
                        </div>

                        <!-- Add Product Button -->
                        <div class="ml-auto">
                            <a href="{{ route('products.create') }}"
                                class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 flex items-center">
                                <span class="mr-2">+</span> List Product
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}"
                        alt="{{ $product->title }}"
                        class="w-full h-48 object-cover">
                    @else
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-500 text-2xl">📦</span>
                    </div>
                    @endif

                    <div class="p-6">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $product->title }}</h3>
                            @if($product->is_free)
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                FREE
                            </span>
                            @else
                            <span class="text-lg font-bold text-blue-600">₹{{ $product->price }}</span>
                            @endif
                        </div>

                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($product->description, 100) }}</p>

                        <div class="flex justify-between items-center text-sm text-gray-500 mb-3">
                            <span>{{ $product->quantity }} {{ $product->unit }}</span>
                            <span class="capitalize {{ $product->condition == 'fresh' ? 'text-green-600' : 'text-orange-600' }}">
                                {{ $product->condition }}
                            </span>
                        </div>

                        <!-- Distance Information -->
                        <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                            <div class="flex items-center">
                                <span class="text-gray-400 mr-1">📍</span>
                                <span>By: {{ $product->user->name }}</span>
                            </div>
                            @if(isset($product->distance))
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                {{ $product->distance }} km away
                            </span>
                            @elseif($product->user->neighborhood)
                            <span class="text-gray-500">{{ $product->user->neighborhood }}</span>
                            @endif
                        </div>

                        <a href="{{ route('products.show', $product) }}"
                            class="w-full bg-blue-500 text-white text-center py-2 rounded-md hover:bg-blue-600 block">
                            View Details
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $products->links() }}
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <div class="text-gray-400 text-6xl mb-4">📦</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                    <p class="text-gray-500 mb-4">
                        @if($userLatitude && $userLongitude)
                        No products found within your selected radius. Try increasing the search radius.
                        @else
                        No products available at the moment.
                        @endif
                    </p>
                    <a href="{{ route('products.create') }}"
                        class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600">
                        List Your First Product
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>