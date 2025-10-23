<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, LogsActivity;

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
        'is_available',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_free' => 'boolean',
        'is_available' => 'boolean',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Update the scope to use is_available
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function getDistanceTo($latitude, $longitude)
    {
        if (!$this->user->latitude || !$this->user->longitude) {
            return null;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($latitude - $this->user->latitude);
        $dLon = deg2rad($longitude - $this->user->longitude);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($this->user->latitude)) * cos(deg2rad($latitude)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return round($earthRadius * $c, 2);
    }
}