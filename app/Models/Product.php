<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

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
        'deleted_reason',
        'restored_at',
        'restored_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_free' => 'boolean',
        'is_available' => 'boolean',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'restored_at' => 'datetime',

    ];

    // Add these accessors and methods for stock management
    protected $appends = ['is_in_stock', 'stock_status', 'stock_status_color'];

    /**
     * Check if product is in stock
     */
    public function getIsInStockAttribute()
    {
        if (!$this->is_available) {
            return false;
        }

        return $this->quantity > 0;
    }

    /**
     * Get stock status text
     */
    public function getStockStatusAttribute()
    {
        if (!$this->is_available) {
            return 'Unavailable';
        }

        if ($this->quantity <= 0) {
            return 'Out of Stock';
        }

        if ($this->quantity <= 5) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    /**
     * Get stock status color
     */
    public function getStockStatusColorAttribute()
    {
        if (!$this->is_available) {
            return 'red';
        }

        if ($this->quantity <= 0) {
            return 'red';
        }

        if ($this->quantity <= 5) {
            return 'yellow';
        }

        return 'green';
    }

    /**
     * Decrement product quantity
     */
    public function decrementStock($amount = 1)
    {
        $this->decrement('quantity', $amount);

        // Auto-update availability if quantity reaches 0
        if ($this->quantity <= 0) {
            $this->update(['is_available' => false]);
        }

        return $this->fresh();
    }

    /**
     * Increment product quantity
     */
    public function incrementStock($amount = 1)
    {
        $this->increment('quantity', $amount);

        // Auto-update availability if quantity goes above 0
        if ($this->quantity > 0 && !$this->is_available) {
            $this->update(['is_available' => true]);
        }

        return $this->fresh();
    }

    /**
     * Check if requested quantity is available
     */
    public function hasSufficientStock($requestedQuantity)
    {
        return $this->is_in_stock && $this->quantity >= $requestedQuantity;
    }

    /**
     * Get available quantity for display
     */
    public function getAvailableQuantityAttribute()
    {
        return $this->is_in_stock ? $this->quantity : 0;
    }

    /**
     * Scope for products that are in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('is_available', true)
            ->where('quantity', '>', 0);
    }

    /**
     * Scope for products with low stock (5 or less)
     */
    public function scopeLowStock($query)
    {
        return $query->where('is_available', true)
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', 5);
    }

    /**
     * Scope for products that are out of stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where(function ($q) {
            $q->where('is_available', false)
                ->orWhere('quantity', '<=', 0);
        });
    }

    /**
     * Safe delete product - preserves data for fraud investigation
     */
    public function safeDelete($reason = 'User deleted account')
    {
        $this->update([
            'is_available' => false,
            'deleted_reason' => $reason
        ]);
        return $this->delete(); // Soft delete
    }

    /**
     * Restore product after deletion
     */
    public function safeRestore()
    {
        $this->restore();
        return $this->update([
            'is_available' => true,
            'deleted_reason' => null
        ]);
    }

    /**
     * Check if product was deleted for fraud reasons
     */
    public function isFraudRelated()
    {
        if (!$this->trashed()) {
            return false;
        }

        $fraudKeywords = ['fraud', 'fake', 'scam', 'counterfeit', 'unauthorized'];
        $reason = strtolower($this->deleted_reason ?? '');

        foreach ($fraudKeywords as $keyword) {
            if (str_contains($reason, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope for deleted products
     */
    public function scopeOnlyTrashedProducts($query)
    {
        return $query->onlyTrashed();
    }

    /**
     * Scope for products including deleted ones
     */
    public function scopeWithTrashedProducts($query)
    {
        return $query->withTrashed();
    }

    /**
     * Get fraud risk level based on deletion reason and history
     */
    public function getFraudRiskLevel()
    {
        if (!$this->trashed()) {
            return 'none';
        }

        if ($this->isFraudRelated()) {
            return 'high';
        }

        $suspiciousReasons = ['violation', 'policy', 'complaint', 'report'];
        $reason = strtolower($this->deleted_reason ?? '');

        foreach ($suspiciousReasons as $keyword) {
            if (str_contains($reason, $keyword)) {
                return 'medium';
            }
        }

        return 'low';
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function exchanges()
    {
        return $this->hasMany(Exchange::class);
    }

    // Update the scope to use is_available and quantity
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where('quantity', '>', 0);
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

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($this->user->latitude)) * cos(deg2rad($latitude)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
