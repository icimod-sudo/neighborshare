<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products Map') }}
            @if($userLatitude && $userLongitude)
            <span class="text-sm font-normal text-green-600 ml-2">
                📍 Showing {{ $products->count() }} products within {{ request('radius', 2) }}km
            </span>
            @endif
        </h2>
    </x-slot>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        #map {
            height: 500px;
        }

        .leaflet-popup-content {
            margin: 8px 12px;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            padding: 0;
        }
    </style>
    @endpush

    <div class="py-6">
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
                                To see products on the map near you, please set your location in your
                                <a href="{{ route('profile.edit') }}" class="underline font-medium">profile</a>.
                                Currently showing all available products.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Map Controls -->
            @if($userLatitude && $userLongitude)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <!-- Radius Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search Radius</label>
                                <select onchange="updateRadius(this.value)"
                                    class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="1" {{ request('radius', 2) == 1 ? 'selected' : '' }}>1 km</option>
                                    <option value="2" {{ request('radius', 2) == 2 ? 'selected' : '' }}>2 km</option>
                                    <option value="5" {{ request('radius', 2) == 5 ? 'selected' : '' }}>5 km</option>
                                    <option value="10" {{ request('radius', 2) == 10 ? 'selected' : '' }}>10 km</option>
                                </select>
                            </div>

                            <!-- Product Count -->
                            <div class="bg-blue-50 px-3 py-2 rounded-lg">
                                <span class="text-sm text-blue-700">
                                    <strong>{{ $products->count() }}</strong> products found
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <a href="{{ route('products.index') }}"
                                class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                                List View
                            </a>
                            <a href="{{ route('products.create') }}"
                                class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors">
                                + List Product
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Map -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div id="map" class="rounded-lg"></div>
                </div>
            </div>

            <!-- Products List -->
            @if($products->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Nearby Products ({{ $products->count() }})
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                alt="{{ $product->title }}"
                                class="w-full h-40 object-cover rounded-lg mb-3">
                            @else
                            <div class="w-full h-40 bg-gray-200 rounded-lg flex items-center justify-center mb-3">
                                <span class="text-gray-400 text-2xl">📦</span>
                            </div>
                            @endif

                            <h4 class="font-semibold text-gray-900 mb-1">{{ $product->title }}</h4>
                            <p class="text-gray-600 text-sm mb-2">{{ $product->subcategory }}</p>

                            <div class="flex justify-between items-center text-sm mb-2">
                                @if($product->is_free)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">FREE</span>
                                @else
                                <span class="font-semibold text-blue-600">₹{{ $product->price }}</span>
                                @endif
                                <span class="text-gray-500">{{ $product->quantity }}{{ $product->unit }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
                                <span>By: {{ $product->user->name }}</span>
                                @if(isset($product->distance))
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    {{ $product->distance }}km away
                                </span>
                                @endif
                            </div>

                            <a href="{{ route('products.show', $product) }}"
                                class="block w-full bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 text-sm">
                                View Details
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <div class="text-gray-400 text-6xl mb-4">🔍</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                    <p class="text-gray-500 mb-4">
                        @if($userLatitude && $userLongitude)
                        No products found within your search radius. Try increasing the radius or listing a product yourself!
                        @else
                        No products available at the moment.
                        @endif
                    </p>
                    <div class="space-x-4">
                        @if($userLatitude && $userLongitude)
                        <a href="{{ route('products.map') }}?radius=5"
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

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map
            const map = L.map('map');

            // Set initial view based on user location or default
            @if($userLatitude && $userLongitude)
            map.setView([{
                {
                    $userLatitude
                }
            }, {
                {
                    $userLongitude
                }
            }], 13);
            @else
            map.setView([20.5937, 78.9629], 5); // Default to India
            @endif

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18,
            }).addTo(map);

            // Add user location marker if available
            @if($userLatitude && $userLongitude)
            const userIcon = L.divIcon({
                className: 'user-marker',
                html: '<div class="w-6 h-6 bg-blue-500 border-2 border-white rounded-full shadow-lg"></div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            L.marker([{
                    {
                        $userLatitude
                    }
                }, {
                    {
                        $userLongitude
                    }
                }])
                .setIcon(userIcon)
                .addTo(map)
                .bindPopup('Your Location')
                .openPopup();
            @endif

            // Add product markers
            @foreach($mapMarkers as $marker)
            @if($marker['icon'] === 'product')
            const productIcon = L.divIcon({
                className: 'product-marker',
                html: `<div class="w-4 h-4 bg-{{ $marker['color'] }}-500 border-2 border-white rounded-full shadow-lg"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            L.marker([{
                    {
                        $marker['lat']
                    }
                }, {
                    {
                        $marker['lon']
                    }
                }])
                .setIcon(productIcon)
                .addTo(map)
                .bindPopup(`{!! $marker['popup'] !!}`);
            @endif
            @endforeach

            // Fit map to show all markers if there are any
            @if(count($mapMarkers) > 0)
            const group = new L.featureGroup([
                @foreach($mapMarkers as $marker)
                L.marker([{
                    {
                        $marker['lat']
                    }
                }, {
                    {
                        $marker['lon']
                    }
                }]),
                @endforeach
            ]);
            map.fitBounds(group.getBounds().pad(0.1));
            @endif
        });

        function updateRadius(radius) {
            const url = new URL(window.location.href);
            url.searchParams.set('radius', radius);
            window.location.href = url.toString();
        }
    </script>
    @endpush
</x-app-layout>