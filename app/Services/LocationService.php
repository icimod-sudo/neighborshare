<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    public function getCoordinatesFromAddress($address)
    {
        $cacheKey = 'geocode_' . md5($address);

        return Cache::remember($cacheKey, 3600, function () use ($address) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'NeighborShare App (http://127.0.0.1:8001)'
                ])->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1,
                ]);

                $data = $response->json();

                if (!empty($data)) {
                    return [
                        'latitude' => (float) $data[0]['lat'],
                        'longitude' => (float) $data[0]['lon'],
                        'address' => $data[0]['display_name'],
                        'neighborhood' => $this->extractNeighborhood($data[0]['address']),
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Geocoding failed: ' . $e->getMessage());
            }

            return null;
        });
    }

    private function extractNeighborhood($address)
    {
        return $address['neighbourhood'] ??
            $address['suburb'] ??
            $address['quarter'] ??
            $address['city_district'] ??
            ($address['city'] ?? null);
    }

    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
