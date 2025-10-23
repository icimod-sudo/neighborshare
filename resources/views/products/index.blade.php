<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Available Products') }}
            @if($userLatitude && $userLongitude)
            <span class="text-sm font-normal text-green-600 ml-2">
                📍 Showing products within {{ request('radius', 2) }}km radius
            </span>
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

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select onchange="updateFilter('category', this.value)"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Categories</option>
                                <option value="vegetable" {{ request('category') == 'vegetable' ? 'selected' : '' }}>Vegetables</option>
                                <option value="fruit" {{ request('category') == 'fruit' ? 'selected' : '' }}>Fruits</option>
                                <option value="plants" {{ request('category') == 'plants' ? 'selected' : '' }}>Plants</option>
                                <option value="fmcg" {{ request('category') == 'fmcg' ? 'selected' : '' }}>FMCG</option>
                                <option value="dairy" {{ request('category') == 'dairy' ? 'selected' : '' }}>Dairy</option>
                                <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Radius Filter (Only show if user has location) -->
                        @if($userLatitude && $userLongitude)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search Radius</label>
                            <select onchange="updateFilter('radius', this.value)"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="1" {{ request('radius', 2) == 1 ? 'selected' : '' }}>1 km</option>
                                <option value="2" {{ request('radius', 2) == 2 ? 'selected' : '' }}>2 km</option>
                                <option value="5" {{ request('radius', 2) == 5 ? 'selected' : '' }}>5 km</option>
                                <option value="10" {{ request('radius', 2) == 10 ? 'selected' : '' }}>10 km</option>
                                <option value="25" {{ request('radius', 2) == 25 ? 'selected' : '' }}>25 km</option>
                            </select>
                        </div>
                        @endif

                        <!-- Free Only Filter -->
                        <div class="flex items-end">
                            <div class="flex items-center">
                                <input type="checkbox" name="free_only" id="free_only" value="1"
                                    {{ request('free_only') ? 'checked' : '' }}
                                    onchange="updateFilter('free_only', this.checked ? 1 : '')"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label for="free_only" class="ms-2 text-sm text-gray-700">Free Items Only</label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-end justify-end space-x-3">
                            <a href="{{ route('products.map') }}"
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center text-sm">
                                <span class="mr-2">🗺️</span> Map View
                            </a>
                            <a href="{{ route('products.create') }}"
                                class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 flex items-center text-sm">
                                <span class="mr-2">+</span> List Product
                            </a>
                        </div>
                    </div>

                    <!-- Search Form -->
                    <div class="mt-4">
                        <form action="{{ route('products.index') }}" method="GET" class="flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Search products...">
                            <button type="submit"
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                                Search
                            </button>
                            @if(request('search') || request('category') || request('radius') || request('free_only'))
                            <a href="{{ route('products.index') }}"
                                class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                                Clear
                            </a>
                            @endif
                        </form>
                    </div>

                    <!-- Active Filters -->
                    @if(request('search') || request('category') || request('free_only') || (request('radius') && request('radius') != 2))
                    <div class="mt-4 flex items-center flex-wrap gap-2">
                        <span class="text-sm text-gray-600">Active filters:</span>
                        @if(request('search'))
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Search: "{{ request('search') }}"
                            <button onclick="removeFilter('search')" class="ml-1 hover:text-blue-600">×</button>
                        </span>
                        @endif
                        @if(request('category'))
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Category: {{ ucfirst(request('category')) }}
                            <button onclick="removeFilter('category')" class="ml-1 hover:text-green-600">×</button>
                        </span>
                        @endif
                        @if(request('free_only'))
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            Free Items Only
                            <button onclick="removeFilter('free_only')" class="ml-1 hover:text-purple-600">×</button>
                        </span>
                        @endif
                        @if(request('radius') && request('radius') != 2)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            Radius: {{ request('radius') }}km
                            <button onclick="removeFilter('radius')" class="ml-1 hover:text-orange-600">×</button>
                        </span>
                        @endif
                    </div>
                    @endif
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
                    <div class="text-gray-400 text-6xl mb-4">🔍</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                    <p class="text-gray-500 mb-4">
                        @if($userLatitude && $userLongitude)
                        No products found within your selected radius. Try increasing the search radius.
                        @else
                        No products available at the moment.
                        @endif
                    </p>
                    <div class="space-x-4">
                        @if($userLatitude && $userLongitude)
                        <a href="{{ route('products.index') }}?radius=5"
                            class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
                            Increase Search Radius
                        </a>
                        @endif
                        <a href="{{ route('products.create') }}"
                            class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600">
                            List a Product
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        // Update filter function
        function updateFilter(filterName, value) {
            const url = new URL(window.location.href);

            if (value === '' || value === null) {
                url.searchParams.delete(filterName);
            } else {
                url.searchParams.set(filterName, value);
            }

            // Remove page parameter when changing filters
            url.searchParams.delete('page');

            window.location.href = url.toString();
        }

        // Remove specific filter
        function removeFilter(filterName) {
            const url = new URL(window.location.href);
            url.searchParams.delete(filterName);
            window.location.href = url.toString();
        }

        // Handle free_only checkbox specifically
        document.addEventListener('DOMContentLoaded', function() {
            const freeOnlyCheckbox = document.getElementById('free_only');
            if (freeOnlyCheckbox) {
                freeOnlyCheckbox.addEventListener('change', function() {
                    updateFilter('free_only', this.checked ? 1 : '');
                });
            }
        });
    </script>
</x-app-layout>