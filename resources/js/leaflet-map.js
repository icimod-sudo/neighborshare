import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix for default markers in Leaflet with Webpack
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

class LeafletMap {
    constructor(mapId) {
        this.mapId = mapId;
        this.map = null;
        this.markers = [];
        this.userMarker = null;

        this.init();
    }

    init() {
        const mapElement = document.getElementById(this.mapId);
        if (!mapElement) return;

        const lat = parseFloat(mapElement.dataset.lat) || 20.5937;
        const lon = parseFloat(mapElement.dataset.lon) || 78.9629;
        const zoom = parseInt(mapElement.dataset.zoom) || 10;
        const markers = JSON.parse(mapElement.dataset.markers || '[]');
        const clickable = mapElement.dataset.clickable === 'true';

        // Initialize map
        this.map = L.map(this.mapId).setView([lat, lon], zoom);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18,
        }).addTo(this.map);

        // Add markers
        this.addMarkers(markers);

        // Add click event if map is clickable
        if (clickable) {
            this.map.on('click', (e) => this.handleMapClick(e));
        }

        // Hide loading spinner
        this.hideLoading();
    }

    addMarkers(markers) {
        markers.forEach(markerData => {
            const marker = L.marker([markerData.lat, markerData.lon])
                .addTo(this.map);

            // Custom icon based on type
            if (markerData.icon === 'user') {
                marker.setIcon(this.createUserIcon());
            } else if (markerData.color) {
                marker.setIcon(this.createColoredIcon(markerData.color));
            }

            // Add popup if available
            if (markerData.popup) {
                marker.bindPopup(markerData.popup, {
                    maxWidth: 300,
                    className: 'custom-popup'
                });
            }

            // Store reference
            if (markerData.icon === 'user') {
                this.userMarker = marker;
            } else {
                this.markers.push(marker);
            }
        });
    }

    createUserIcon() {
        return L.divIcon({
            className: 'user-location-marker',
            html: '<div class="w-6 h-6 bg-blue-500 border-2 border-white rounded-full shadow-lg"></div>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
    }

    createColoredIcon(color) {
        const colorMap = {
            green: '#10B981',
            orange: '#F59E0B',
            red: '#EF4444',
            blue: '#3B82F6',
            purple: '#8B5CF6'
        };

        return L.divIcon({
            className: 'product-marker',
            html: `<div class="w-4 h-4 bg-${color}-500 border-2 border-white rounded-full shadow-lg"></div>`,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });
    }

    handleMapClick(e) {
        const { lat, lng } = e.latlng;
        const onClickFunction = document.getElementById(this.mapId).dataset.onclick;

        if (onClickFunction && typeof window[onClickFunction] === 'function') {
            window[onClickFunction](lat, lng);
        }
    }

    locateUser() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            return;
        }

        this.showLoading();

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Remove existing user marker
                if (this.userMarker) {
                    this.map.removeLayer(this.userMarker);
                }

                // Add new user marker
                this.userMarker = L.marker([lat, lng])
                    .setIcon(this.createUserIcon())
                    .addTo(this.map)
                    .bindPopup('Your current location')
                    .openPopup();

                // Center map on user location
                this.map.setView([lat, lng], 15);

                this.hideLoading();
            },
            (error) => {
                this.hideLoading();
                alert('Unable to retrieve your location: ' + error.message);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    }

    showLoading() {
        const loadingElement = document.getElementById(`map-loading-${this.mapId}`);
        if (loadingElement) {
            loadingElement.classList.remove('hidden');
        }
    }

    hideLoading() {
        const loadingElement = document.getElementById(`map-loading-${this.mapId}`);
        if (loadingElement) {
            loadingElement.classList.add('hidden');
        }
    }

    addMarker(lat, lng, options = {}) {
        const marker = L.marker([lat, lng]).addTo(this.map);

        if (options.popup) {
            marker.bindPopup(options.popup);
        }

        this.markers.push(marker);
        return marker;
    }

    clearMarkers() {
        this.markers.forEach(marker => {
            this.map.removeLayer(marker);
        });
        this.markers = [];
    }

    setView(lat, lng, zoom = 15) {
        this.map.setView([lat, lng], zoom);
    }

    getCenter() {
        return this.map.getCenter();
    }

    destroy() {
        if (this.map) {
            this.map.remove();
        }
    }
}

// Global functions for map controls
window.locateUser = function (mapId) {
    if (window.leafletMaps && window.leafletMaps[mapId]) {
        window.leafletMaps[mapId].locateUser();
    }
};

window.toggleFullscreen = function (mapId) {
    const mapElement = document.getElementById(mapId);
    mapElement.classList.toggle('fixed');
    mapElement.classList.toggle('inset-0');
    mapElement.classList.toggle('z-50');

    if (window.leafletMaps && window.leafletMaps[mapId]) {
        window.leafletMaps[mapId].map.invalidateSize();
    }
};

// Initialize all maps on page load
document.addEventListener('DOMContentLoaded', function () {
    window.leafletMaps = {};

    document.querySelectorAll('[id^="map"]').forEach(mapElement => {
        const mapId = mapElement.id;
        window.leafletMaps[mapId] = new LeafletMap(mapId);
    });
});

export default LeafletMap;