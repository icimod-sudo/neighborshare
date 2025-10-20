<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'subcategory',
        'quantity',
        'unit',
        'condition',
        'price',
        'is_free',
        'image',
        'expiry_date',
        'is_available'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exchanges()
    {
        return $this->hasMany(Exchange::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopeHasLocation($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->whereNotNull('latitude')->whereNotNull('longitude');
        });
    }

    // Calculate distance for this product
    public function getDistanceTo($lat, $lon)
    {
        if (!$this->user || !$this->user->latitude || !$this->user->longitude) {
            return null;
        }

        return $this->calculateDistance(
            $this->user->latitude,
            $this->user->longitude,
            $lat,
            $lon
        );
    }

    // Haversine formula
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
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
