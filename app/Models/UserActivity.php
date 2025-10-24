<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'description',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'metadata',
        'performed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime'
    ];

    /**
     * Get the user that performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope by activity type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for login activities
     */
    public function scopeLogins($query)
    {
        return $query->where('type', 'login');
    }

    /**
     * Scope for product activities
     */
    public function scopeProductActivities($query)
    {
        return $query->where('type', 'like', 'product_%');
    }

    /**
     * Scope for exchange activities
     */
    public function scopeExchangeActivities($query)
    {
        return $query->where('type', 'like', 'exchange_%');
    }

    /**
     * Get device icon
     */
    public function getDeviceIcon(): string
    {
        return match ($this->device_type) {
            'mobile' => '📱',
            'tablet' => '📟',
            'desktop' => '💻',
            default => '🖥️'
        };
    }

    /**
     * Get activity icon
     */
    public function getActivityIcon(): string
    {
        return match ($this->type) {
            'login' => '🔐',
            'logout' => '🚪',
            'product_view' => '👀',
            'product_create' => '➕',
            'product_update' => '✏️',
            'product_delete' => '🗑️',
            'exchange_request' => '🔄',
            'exchange_accept' => '✅',
            'exchange_complete' => '🏁',
            'exchange_cancel' => '❌',
            'profile_update' => '👤',
            'location_update' => '📍',
            default => '📝'
        };
    }
}
