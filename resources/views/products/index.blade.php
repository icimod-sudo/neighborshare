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

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
            <!-- Location Alert -->
            @if(!$userLatitude || !$userLongitude)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0 pt-0.5">
                        <span class="text-yellow-400 text-xl">📍</span>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Location Not Set
                        </h3>
                        <div class="mt-1 text-sm text-yellow-700">
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
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <!-- Category Filter -->
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select onchange="updateFilter('category', this.value)"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm sm:text-base h-10">
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
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search Radius</label>
                            <select onchange="updateFilter('radius', this.value)"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm sm:text-base h-10">
                                <option value="1" {{ request('radius', 2) == 1 ? 'selected' : '' }}>1 km</option>
                                <option value="2" {{ request('radius', 2) == 2 ? 'selected' : '' }}>2 km</option>
                                <option value="5" {{ request('radius', 2) == 5 ? 'selected' : '' }}>5 km</option>
                                <option value="10" {{ request('radius', 2) == 10 ? 'selected' : '' }}>10 km</option>
                                <option value="25" {{ request('radius', 2) == 25 ? 'selected' : '' }}>25 km</option>
                            </select>
                        </div>
                        @endif

                        <!-- Free Only Filter -->
                        <div class="flex items-center sm:col-span-1 lg:col-span-1">
                            <div class="flex items-center h-10">
                                <input type="checkbox" name="free_only" id="free_only" value="1"
                                    {{ request('free_only') ? 'checked' : '' }}
                                    onchange="updateFilter('free_only', this.checked ? 1 : '')"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 h-4 w-4">
                                <label for="free_only" class="ms-2 text-sm text-gray-700 whitespace-nowrap">Free Items Only</label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-2 sm:space-x-3 sm:col-span-2 lg:col-span-1 lg:justify-end">
                            <a href="{{ route('products.map') }}"
                                class="flex-1 bg-blue-500 text-white px-3 py-2 rounded-md hover:bg-blue-600 flex items-center justify-center text-sm min-h-[44px]">
                                <span class="mr-2">🗺️</span>
                                <span class="hidden sm:inline">Map View</span>
                                <span class="sm:hidden">Map</span>
                            </a>
                            <a href="{{ route('products.create') }}"
                                class="flex-1 bg-green-500 text-white px-3 py-2 rounded-md hover:bg-green-600 flex items-center justify-center text-sm min-h-[44px]">
                                <span class="mr-2">+</span>
                                <span class="hidden sm:inline">List Product</span>
                                <span class="sm:hidden">List</span>
                            </a>
                        </div>
                    </div>

                    <!-- Search Form -->
                    <div class="mt-4">
                        <form action="{{ route('products.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm sm:text-base h-10 px-3"
                                placeholder="Search products...">
                            <div class="flex gap-2">
                                <button type="submit"
                                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex-1 min-h-[44px] text-sm sm:text-base">
                                    Search
                                </button>
                                @if(request('search') || request('category') || request('radius') || request('free_only'))
                                <a href="{{ route('products.index') }}"
                                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center justify-center min-h-[44px] text-sm sm:text-base">
                                    Clear
                                </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Active Filters -->
                    @if(request('search') || request('category') || request('free_only') || (request('radius') && request('radius') != 2))
                    <div class="mt-4 flex items-center flex-wrap gap-2">
                        <span class="text-sm text-gray-600 hidden sm:inline">Active filters:</span>
                        <span class="text-sm text-gray-600 sm:hidden">Filters:</span>
                        @if(request('search'))
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Search: "{{ request('search') }}"
                            <button onclick="removeFilter('search')" class="ml-1 hover:text-blue-600 text-sm min-h-[20px]">×</button>
                        </span>
                        @endif
                        @if(request('category'))
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ ucfirst(request('category')) }}
                            <button onclick="removeFilter('category')" class="ml-1 hover:text-green-600 text-sm min-h-[20px]">×</button>
                        </span>
                        @endif
                        @if(request('free_only'))
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            Free Only
                            <button onclick="removeFilter('free_only')" class="ml-1 hover:text-purple-600 text-sm min-h-[20px]">×</button>
                        </span>
                        @endif
                        @if(request('radius') && request('radius') != 2)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            {{ request('radius') }}km
                            <button onclick="removeFilter('radius')" class="ml-1 hover:text-orange-600 text-sm min-h-[20px]">×</button>
                        </span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($products as $product)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow border border-gray-100">
                    @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}"
                        alt="{{ $product->title }}"
                        class="w-full h-40 sm:h-48 object-cover">
                    @else
                    <div class="w-full h-40 sm:h-48 bg-gray-100 flex items-center justify-center">
                        <span class="text-gray-400 text-2xl">📦</span>
                    </div>
                    @endif

                    <div class="p-4 sm:p-6">
                        <div class="flex justify-between items-start mb-2 gap-2">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 line-clamp-2 flex-1">{{ $product->title }}</h3>
                            @if($product->is_free)
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded flex-shrink-0">
                                FREE
                            </span>
                            @else
                            <span class="text-base sm:text-lg font-bold text-blue-600 flex-shrink-0">रू {{ $product->price }}</span>
                            @endif
                        </div>

                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ Str::limit($product->description, 80) }}</p>

                        <div class="flex justify-between items-center text-sm text-gray-500 mb-3 flex-wrap gap-1">
                            <span class="bg-gray-100 px-2 py-1 rounded">{{ $product->quantity }} {{ $product->unit }}</span>
                            <span class="capitalize px-2 py-1 rounded {{ $product->condition == 'fresh' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ $product->condition }}
                            </span>
                        </div>

                        <!-- Distance Information -->
                        <div class="flex justify-between items-center text-sm text-gray-500 mb-4 flex-wrap gap-2">
                            <div class="flex items-center text-xs sm:text-sm">
                                <span class="text-gray-400 mr-1">📍</span>
                                <span class="truncate max-w-[100px] sm:max-w-none">By: {{ $product->user->name }}</span>
                            </div>
                            @if(isset($product->distance))
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs whitespace-nowrap">
                                {{ $product->distance }} km away
                            </span>
                            @elseif($product->user->neighborhood)
                            <span class="text-gray-500 text-xs sm:text-sm truncate max-w-[80px] sm:max-w-none">{{ $product->user->neighborhood }}</span>
                            @endif
                        </div>

                        <a href="{{ route('products.show', $product) }}"
                            class="w-full bg-blue-500 text-white text-center py-2 rounded-md hover:bg-blue-600 block text-sm sm:text-base min-h-[44px] flex items-center justify-center">
                            View Details
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6 px-4 sm:px-0">
                {{ $products->links() }}
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-12 text-center">
                    <div class="text-gray-400 text-4xl sm:text-6xl mb-4">🔍</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                    <p class="text-gray-500 mb-4 text-sm sm:text-base">
                        @if($userLatitude && $userLongitude)
                        No products found within your selected radius. Try increasing the search radius.
                        @else
                        No products available at the moment.
                        @endif
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        @if($userLatitude && $userLongitude)
                        <a href="{{ route('products.index') }}?radius=5"
                            class="bg-blue-500 text-white px-4 sm:px-6 py-2 rounded-md hover:bg-blue-600 text-sm sm:text-base min-h-[44px] flex items-center justify-center">
                            Increase Search Radius
                        </a>
                        @endif
                        <a href="{{ route('products.create') }}"
                            class="bg-green-500 text-white px-4 sm:px-6 py-2 rounded-md hover:bg-green-600 text-sm sm:text-base min-h-[44px] flex items-center justify-center">
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

        // Add line-clamp utility if not present
        const style = document.createElement('style');
        style.textContent = `
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);
    </script>
</x-app-layout>