<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Exchange extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'product_id',
        'from_user_id',
        'to_user_id',
        'status',
        'type',
        'agreed_price',
        'message',
        'exchange_date',
        'requested_quantity',
        'contact_info',
        'deleted_reason'
    ];

    protected $casts = [
        'exchange_date' => 'datetime',
        'agreed_price' => 'decimal:2',
        'requested_quantity' => 'decimal:2',
    ];

    // Stock management methods
    public function acceptWithStockManagement()
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $product = $this->product;

        // Check if product has sufficient stock
        if (!$product->hasSufficientStock($this->requested_quantity)) {
            return false;
        }

        // Update product quantity
        $product->decrementStock($this->requested_quantity);

        // Update exchange status
        $this->update([
            'status' => 'accepted',
            'exchange_date' => now()->addDays(1)
        ]);

        return true;
    }

    public function cancelWithStockManagement()
    {
        $previousStatus = $this->status;
        $this->update(['status' => 'cancelled']);

        // If the exchange was accepted, return the stock
        if ($previousStatus === 'accepted') {
            $this->product->incrementStock($this->requested_quantity);
        }

        return true;
    }

    /**
     * Safe cancel exchange - preserves data for fraud investigation
     */
    public function safeCancel($reason = 'User deleted account')
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'cancelled',
            'deleted_reason' => $reason
        ]);

        // If the exchange was accepted, return the stock
        if ($previousStatus === 'accepted') {
            $this->product->incrementStock($this->requested_quantity);
        }

        return $this->delete(); // Soft delete
    }

    /**
     * Restore exchange after deletion
     */
    public function safeRestore()
    {
        $this->restore();
        return $this->update([
            'deleted_reason' => null
        ]);
    }

    public function canBeAccepted()
    {
        return $this->status === 'pending' &&
            $this->product->hasSufficientStock($this->requested_quantity);
    }

    public function getDisplayQuantityAttribute()
    {
        return $this->requested_quantity . ' ' . $this->product->unit;
    }

    /**
     * Check if exchange involves fraudulent users
     */
    public function involvesFraudulentUsers()
    {
        return $this->fromUser->isBanned() || $this->toUser->isBanned() ||
            $this->fromUser->isSuspended() || $this->toUser->isSuspended() ||
            $this->fromUser->getFraudScore() >= 5 || $this->toUser->getFraudScore() >= 5;
    }

    /**
     * Get fraud risk level for this exchange
     */
    public function getFraudRiskLevel()
    {
        if ($this->involvesFraudulentUsers()) {
            return 'high';
        }

        if ($this->product->isFraudRelated()) {
            return 'high';
        }

        if ($this->trashed() && $this->isFraudRelated()) {
            return 'high';
        }

        return 'low';
    }

    /**
     * Check if exchange was deleted for fraud reasons
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
     * Scope for deleted exchanges
     */
    public function scopeOnlyTrashedExchanges($query)
    {
        return $query->onlyTrashed();
    }

    /**
     * Scope for exchanges including deleted ones
     */
    public function scopeWithTrashedExchanges($query)
    {
        return $query->withTrashed();
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id')->withTrashed();
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id')->withTrashed();
    }
}
