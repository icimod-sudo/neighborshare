<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div>
                <p class="text-sm mt-2 text-gray-800">
                    {{ __('Your email address is unverified.') }}

                    <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                <p class="mt-2 font-medium text-sm text-green-600">
                    {{ __('A new verification link has been sent to your email address.') }}
                </p>
                @endif
            </div>
            @endif
        </div>

        <!-- Phone Number -->
        <div>
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <!-- Location Section -->
        <div class="border-t pt-6 mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">📍 Location Information</h3>
            <p class="text-sm text-gray-600 mb-4">
                Set your location to see nearby products on the map and help others find you.
            </p>

            <!-- Address -->
            <div class="mb-4">
                <div class="flex justify-between items-center">
                    <x-input-label for="address" :value="__('Full Address')" />
                    <button type="button" id="detect-location"
                        class="text-sm text-blue-600 hover:text-blue-900 flex items-center">
                        <span class="mr-1">📍</span> Detect My Location
                    </button>
                </div>
                <x-text-input id="address" name="address" type="text" class="mt-1 block w-full"
                    :value="old('address', $user->address)" autocomplete="street-address"
                    placeholder="Enter your full address for accurate location" />
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <!-- Neighborhood -->
            <div class="mb-4">
                <x-input-label for="neighborhood" :value="__('Neighborhood/Area Name')" />
                <x-text-input id="neighborhood" name="neighborhood" type="text" class="mt-1 block w-full"
                    :value="old('neighborhood', $user->neighborhood)"
                    placeholder="e.g., Koramangala, Bandra West, etc." />
                <x-input-error class="mt-2" :messages="$errors->get('neighborhood')" />
            </div>

            <!-- Manual Location Selection -->
            <div class="mb-4">
                <x-input-label for="location-map" :value="__('Or Select Location on Map')" />
                <div id="location-map" class="mt-1 h-48 rounded-lg border border-gray-300"></div>
                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $user->latitude) }}">
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $user->longitude) }}">
                <p class="text-xs text-gray-500 mt-1">Click on the map to set your exact location</p>
            </div>

            <!-- Current Location Status -->
            <div id="location-status" class="text-sm p-3 rounded-lg 
                @if($user->latitude && $user->longitude) bg-green-50 text-green-700 border border-green-200
                @else bg-yellow-50 text-yellow-700 border border-yellow-200 @endif">
                @if($user->latitude && $user->longitude)
                ✅ Location is set: {{ $user->latitude }}, {{ $user->longitude }}
                @else
                ⚠️ Location not set. Please set your location to use map features.
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<style>
    #location-map {
        height: 200px;
        z-index: 1;
    }

    .location-marker {
        background: transparent;
        border: none;
    }
    
    /* Ensure map container is visible */
    .leaflet-container {
        height: 100%;
        width: 100%;
        border-radius: 0.375rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let map;
        let marker;
        
        // Fix: Proper Blade syntax for PHP variables in JavaScript
        let userLat = {{ $user->latitude ?? 'null' }};
        let userLon = {{ $user->longitude ?? 'null' }};

        // Initialize map
        function initMap() {
            // Default center (India)
            const defaultLat = 20.5937;
            const defaultLon = 78.9629;

            // Use user's location if available, otherwise use default
            const centerLat = userLat || defaultLat;
            const centerLon = userLon || defaultLon;
            const zoom = userLat ? 13 : 5;

            // Check if map container exists
            const mapContainer = document.getElementById('location-map');
            if (!mapContainer) {
                console.error('Map container not found');
                return;
            }

            // Initialize the map
            map = L.map('location-map').setView([centerLat, centerLon], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18,
            }).addTo(map);

            // Add marker if user has location
            if (userLat && userLon) {
                marker = L.marker([userLat, userLon]).addTo(map);
                
                // Add a popup to the marker
                marker.bindPopup('Your current location').openPopup();
            }

            // Add click event to set location
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                // Update hidden inputs
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                // Remove existing marker
                if (marker) {
                    map.removeLayer(marker);
                }

                // Add new marker
                marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup('Selected location').openPopup();

                // Update status
                updateLocationStatus(true, lat, lng);

                // Reverse geocode to get address
                reverseGeocode(lat, lng);
            });

            // Fix map rendering issues
            setTimeout(() => {
                map.invalidateSize();
            }, 100);
        }

        // Reverse geocode coordinates to address
        function reverseGeocode(lat, lng) {
            // Show loading state
            const statusElement = document.getElementById('location-status');
            statusElement.innerHTML = '🔄 Getting address...';
            statusElement.className = 'text-sm p-3 rounded-lg bg-blue-50 text-blue-700 border border-blue-200';

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById('address').value = data.display_name;

                        // Try to extract neighborhood
                        const address = data.address;
                        const neighborhood = address.neighbourhood ||
                            address.suburb ||
                            address.quarter ||
                            address.city_district ||
                            address.city;
                        if (neighborhood && !document.getElementById('neighborhood').value) {
                            document.getElementById('neighborhood').value = neighborhood;
                        }
                        
                        updateLocationStatus(true, lat, lng);
                    }
                })
                .catch(error => {
                    console.error('Reverse geocoding failed:', error);
                    updateLocationStatus(true, lat, lng); // Still update status even if geocoding fails
                });
        }

        // Update location status display
        function updateLocationStatus(isSet, lat = null, lon = null) {
            const statusElement = document.getElementById('location-status');
            if (isSet && lat && lon) {
                statusElement.innerHTML = `✅ Location set: ${lat.toFixed(6)}, ${lon.toFixed(6)}`;
                statusElement.className = 'text-sm p-3 rounded-lg bg-green-50 text-green-700 border border-green-200';
            } else {
                statusElement.innerHTML = '⚠️ Location not set. Please set your location to use map features.';
                statusElement.className = 'text-sm p-3 rounded-lg bg-yellow-50 text-yellow-700 border border-yellow-200';
            }
        }

        // Browser location detection
        document.getElementById('detect-location').addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }

            // Show loading state
            const button = this;
            const originalText = button.innerHTML;
            button.innerHTML = '🔄 Detecting...';
            button.disabled = true;

            // Update status
            const statusElement = document.getElementById('location-status');
            statusElement.innerHTML = '🔄 Detecting your location...';
            statusElement.className = 'text-sm p-3 rounded-lg bg-blue-50 text-blue-700 border border-blue-200';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // Update hidden inputs
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;

                    // Update map view and marker
                    if (map) {
                        map.setView([lat, lng], 15);

                        // Remove existing marker
                        if (marker) {
                            map.removeLayer(marker);
                        }

                        // Add new marker
                        marker = L.marker([lat, lng]).addTo(map);
                        marker.bindPopup('Your current location').openPopup();
                    }

                    // Update status
                    updateLocationStatus(true, lat, lng);

                    // Reverse geocode to get address
                    reverseGeocode(lat, lng);

                    // Restore button
                    button.innerHTML = originalText;
                    button.disabled = false;
                },
                function(error) {
                    let errorMessage = 'Unable to retrieve your location. ';

                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Please allow location access in your browser settings.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Location information is unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Location request timed out.';
                            break;
                        default:
                            errorMessage += 'An unknown error occurred.';
                    }

                    alert(errorMessage);

                    // Restore button and status
                    button.innerHTML = originalText;
                    button.disabled = false;
                    updateLocationStatus(false);
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        });

        // Initialize map when page loads
        initMap();

        // Update location status when address is manually entered
        document.getElementById('address').addEventListener('blur', function() {
            const address = this.value.trim();
            if (address && address.length > 5) { // Only geocode if address is meaningful
                // Show loading state
                const statusElement = document.getElementById('location-status');
                statusElement.innerHTML = '🔄 Geocoding address...';
                statusElement.className = 'text-sm p-3 rounded-lg bg-blue-50 text-blue-700 border border-blue-200';

                // Geocode the address to get coordinates
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&addressdetails=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lon = parseFloat(data[0].lon);

                            document.getElementById('latitude').value = lat;
                            document.getElementById('longitude').value = lon;

                            // Update map
                            if (map) {
                                map.setView([lat, lon], 15);

                                if (marker) {
                                    map.removeLayer(marker);
                                }
                                marker = L.marker([lat, lon]).addTo(map);
                                marker.bindPopup('Location from address').openPopup();
                            }

                            // Extract neighborhood from geocoding result
                            if (data[0].address) {
                                const addressData = data[0].address;
                                const neighborhood = addressData.neighbourhood ||
                                                    addressData.suburb ||
                                                    addressData.quarter ||
                                                    addressData.city_district ||
                                                    addressData.city;
                                if (neighborhood && !document.getElementById('neighborhood').value) {
                                    document.getElementById('neighborhood').value = neighborhood;
                                }
                            }

                            updateLocationStatus(true, lat, lon);
                        } else {
                            updateLocationStatus(false);
                            alert('Could not find coordinates for this address. Please try a more specific address.');
                        }
                    })
                    .catch(error => {
                        console.error('Geocoding failed:', error);
                        updateLocationStatus(false);
                        alert('Error geocoding address. Please try again.');
                    });
            }
        });
        
        // Handle form submission to ensure location is set
        document.querySelector('form').addEventListener('submit', function(e) {
            const lat = document.getElementById('latitude').value;
            const lon = document.getElementById('longitude').value;
            const address = document.getElementById('address').value;
            
            // If address is provided but no coordinates, try to geocode first
            if (address && (!lat || !lon)) {
                e.preventDefault(); // Prevent form submission
                
                // Show loading message
                const statusElement = document.getElementById('location-status');
                statusElement.innerHTML = '🔄 Setting location from address before saving...';
                statusElement.className = 'text-sm p-3 rounded-lg bg-blue-50 text-blue-700 border border-blue-200';
                
                // Geocode the address
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const newLat = parseFloat(data[0].lat);
                            const newLon = parseFloat(data[0].lon);
                            
                            document.getElementById('latitude').value = newLat;
                            document.getElementById('longitude').value = newLon;
                            
                            // Now submit the form
                            document.querySelector('form').submit();
                        } else {
                            alert('Could not determine location from address. Please set location manually on the map.');
                            updateLocationStatus(false);
                        }
                    })
                    .catch(error => {
                        console.error('Geocoding failed:', error);
                        alert('Error setting location from address. Please set location manually on the map.');
                        updateLocationStatus(false);
                    });
            }
        });
    });
</script>
@endpush