<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;


class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'latitude',
        'longitude',
        'neighborhood',
        'rating',
        'total_exchanges',
        'is_admin'

    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',

        ];
    }

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sentExchanges()
    {
        return $this->hasMany(Exchange::class, 'from_user_id');
    }

    public function receivedExchanges()
    {
        return $this->hasMany(Exchange::class, 'to_user_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Simple location scope - just check if user has location
    public function scopeHasLocation($query)
    {
        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }

    // Calculate distance for a specific user (used in PHP, not SQL)
    public function getDistanceTo($lat, $lon)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        return $this->calculateDistance($this->latitude, $this->longitude, $lat, $lon);
    }

    // Haversine formula for distance calculation
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
