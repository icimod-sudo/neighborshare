<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products Map') }}
            @if($userLatitude && $userLongitude)
                <span class="text-sm font-normal text-green-600 ml-2">
                    📍 Showing {{ $products->count() }} products within {{ $radius }}km
                </span>
            @endif
        </h2>
    </x-slot>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        #map {
            height: 500px;
            width: 100%;
            border-radius: 8px;
            z-index: 1;
        }
        
        #mapContainer {
            position: relative;
            height: 500px;
        }
        
        #mapLoading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(243, 244, 246, 0.9);
            border-radius: 8px;
        }
        
        #mapError {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 3;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(254, 226, 226, 0.9);
            border-radius: 8px;
        }

        .leaflet-popup-content {
            margin: 8px 12px;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            padding: 0;
        }
        
        .loading-spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            #map, #mapContainer {
                height: 400px;
            }
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

            <!-- Map Controls - Only show if user has location -->
            @if($userLatitude && $userLongitude)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <!-- Radius Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search Radius</label>
                                <select id="radiusFilter" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="1" {{ $radius == 1 ? 'selected' : '' }}>1 km</option>
                                    <option value="2" {{ $radius == 2 ? 'selected' : '' }}>2 km</option>
                                    <option value="5" {{ $radius == 5 ? 'selected' : '' }}>5 km</option>
                                    <option value="10" {{ $radius == 10 ? 'selected' : '' }}>10 km</option>
                                    <option value="25" {{ $radius == 25 ? 'selected' : '' }}>25 km</option>
                                </select>
                            </div>

                            <!-- Product Count -->
                            <div class="bg-blue-50 px-3 py-2 rounded-lg">
                                <span class="text-sm text-blue-700">
                                    <strong>{{ $products->count() }}</strong> products found
                                </span>
                            </div>

                            <!-- Current Radius Display -->
                            <div class="bg-green-50 px-3 py-2 rounded-lg">
                                <span class="text-sm text-green-700">
                                    Current: {{ $radius }}km radius
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <a href="{{ route('products.index') }}?radius={{ $radius }}"
                                class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                                List View
                            </a>
                            <a href="{{ route('products.create') }}"
                                class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors">
                                + List Product
                            </a>
                        </div>
                    </div>

                    <!-- Active Filters -->
                    @if($radius != 2)
                    <div class="mt-4 flex items-center flex-wrap gap-2">
                        <span class="text-sm text-gray-600">Active filters:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            Radius: {{ $radius }}km
                            <button onclick="removeFilter('radius')" class="ml-1 hover:text-orange-600">×</button>
                        </span>
                        <a href="{{ route('products.map') }}" 
                           class="text-sm text-blue-600 hover:text-blue-900 ml-2">
                            Clear all
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Map Container -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div id="mapContainer" class="relative rounded-lg">
                        <!-- Loading Indicator -->
                        <div id="mapLoading">
                            <div class="text-center">
                                <div class="loading-spinner rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                                <p class="mt-2 text-gray-600">Loading map...</p>
                            </div>
                        </div>
                        
                        <!-- Map Container -->
                        <div id="map"></div>
                        
                        <!-- Error Message -->
                        <div id="mapError">
                            <div class="text-center">
                                <div class="text-red-500 text-4xl mb-2">❌</div>
                                <h3 class="text-lg font-medium text-red-800 mb-2">Failed to load map</h3>
                                <p class="text-red-600 mb-4">Please check your internet connection and try again.</p>
                                <button onclick="initializeMap()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                    Retry
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products List -->
            @if($products->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        @if($userLatitude && $userLongitude)
                            Nearby Products ({{ $products->count() }} within {{ $radius }}km)
                        @else
                            All Products ({{ $products->count() }})
                        @endif
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
                                    {{ number_format($product->distance, 1) }}km away
                                </span>
                                @elseif($product->user->neighborhood)
                                <span class="text-gray-500">{{ $product->user->neighborhood }}</span>
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
                        No products found within {{ $radius }}km radius. Try increasing the search radius.
                        @else
                        No products available at the moment.
                        @endif
                    </p>
                    <div class="space-x-4">
                        @if($userLatitude && $userLongitude)
                        <button onclick="updateRadius(5)" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
                            Try 5km Radius
                        </button>
                        <button onclick="updateRadius(10)" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
                            Try 10km Radius
                        </button>
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
        // Global variables
        let map = null;
        let markers = [];

        // Utility functions for UI states
        function showLoading() {
            document.getElementById('mapLoading').style.display = 'flex';
            document.getElementById('mapError').style.display = 'none';
        }

        function showMap() {
            document.getElementById('mapLoading').style.display = 'none';
            document.getElementById('mapError').style.display = 'none';
        }

        function showError() {
            document.getElementById('mapLoading').style.display = 'none';
            document.getElementById('mapError').style.display = 'flex';
        }

        // Radius update function
        function updateRadius(radius) {
            const url = new URL(window.location.href);
            
            if (radius && radius !== '') {
                url.searchParams.set('radius', radius);
            } else {
                url.searchParams.delete('radius');
            }
            
            window.location.href = url.toString();
        }

        // Remove specific filter
        function removeFilter(filterName) {
            const url = new URL(window.location.href);
            url.searchParams.delete(filterName);
            window.location.href = url.toString();
        }

        // Clear all markers from map
        function clearMarkers() {
            markers.forEach(marker => {
                if (map && marker) {
                    map.removeLayer(marker);
                }
            });
            markers = [];
        }

        // Initialize the map
        function initializeMap() {
            console.log('🚀 Initializing map...');
            showLoading();

            try {
                // Check if map container exists
                const mapContainer = document.getElementById('map');
                if (!mapContainer) {
                    throw new Error('Map container not found');
                }

                // Clear existing map if any
                if (map) {
                    map.remove();
                    map = null;
                }

                // Clear existing markers
                clearMarkers();

                // Initialize new map
                map = L.map('map');
                console.log('✅ Map initialized');

                // Set initial view
                @if($userLatitude && $userLongitude)
                    console.log('📍 Setting view to user location:', {{ $userLatitude }}, {{ $userLongitude }});
                    map.setView([{{ $userLatitude }}, {{ $userLongitude }}], 13);
                @else
                    console.log('🌍 Setting view to default location');
                    map.setView([27.7172, 85.3240], 12); // Kathmandu center
                @endif

                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 18,
                }).addTo(map);
                console.log('✅ Tile layer added');

                // Create custom icons
                const userIcon = L.divIcon({
                    className: 'user-marker',
                    html: '<div style="width: 24px; height: 24px; background: #3b82f6; border: 3px solid white; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const productIcon = L.divIcon({
                    className: 'product-marker',
                    html: '<div style="width: 20px; height: 20px; background: #f59e0b; border: 2px solid white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                const freeProductIcon = L.divIcon({
                    className: 'free-product-marker',
                    html: '<div style="width: 20px; height: 20px; background: #10b981; border: 2px solid white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                // Add user location marker if available
                @if($userLatitude && $userLongitude)
                const userMarker = L.marker([{{ $userLatitude }}, {{ $userLongitude }}], { 
                    icon: userIcon 
                }).addTo(map).bindPopup(`
                    <div class="p-2">
                        <strong>Your Location</strong><br>
                        <small>This is where you're viewing from</small>
                    </div>
                `);
                markers.push(userMarker);
                console.log('✅ User marker added');
                @endif

                // Add product markers
                @foreach($mapMarkers as $marker)
                    const icon{{ $loop->index }} = '{{ $marker['color'] }}' === 'green' ? freeProductIcon : productIcon;
                    
                    const productMarker{{ $loop->index }} = L.marker([{{ $marker['lat'] }}, {{ $marker['lon'] }}], { 
                        icon: icon{{ $loop->index }}
                    }).addTo(map).bindPopup(`{!! $marker['popup'] !!}`);
                    
                    markers.push(productMarker{{ $loop->index }});
                @endforeach
                console.log('✅ Added {{ count($mapMarkers) }} product markers');

                // Fit map to show all markers if there are any
                if (markers.length > 0) {
                    const group = L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                    console.log('✅ Map fitted to bounds');
                } else {
                    console.log('ℹ️ No markers to display');
                }

                // Fix map rendering issues after a short delay
                setTimeout(() => {
                    map.invalidateSize();
                    console.log('✅ Map size invalidated');
                    
                    // Show map after successful initialization
                    showMap();
                }, 500);

            } catch (error) {
                console.error('❌ Map initialization failed:', error);
                showError();
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 DOM loaded, initializing map...');
            
            // Initialize map after a short delay to ensure everything is rendered
            setTimeout(() => {
                initializeMap();
            }, 100);

            // Add event listener to radius filter
            const radiusFilter = document.getElementById('radiusFilter');
            if (radiusFilter) {
                radiusFilter.addEventListener('change', function() {
                    console.log('🔄 Radius filter changed to:', this.value);
                    updateRadius(this.value);
                });
            }

            // Add resize event listener to fix map on window resize
            window.addEventListener('resize', function() {
                if (map) {
                    setTimeout(() => {
                        map.invalidateSize();
                    }, 250);
                }
            });
        });

        // Make functions available globally for onclick handlers
        window.updateRadius = updateRadius;
        window.removeFilter = removeFilter;
        window.initializeMap = initializeMap;
    </script>
    @endpush
</x-app-layout>