<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

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
        'is_admin',
        'deleted_reason',
        'deleted_by',
        'suspended_until',
        'suspension_reason',
        'strike_count',
        'fraud_flags'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'suspended_until' => 'datetime',
            'fraud_flags' => 'array',
            'rating' => 'decimal:2',
        ];
    }

    // Relationships - Keep simple without withTrashed()
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

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Scopes
    public function scopeHasLocation($query)
    {
        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('suspended_until')
                    ->orWhere('suspended_until', '<', now());
            });
    }

    public function scopeSuspended($query)
    {
        return $query->whereNotNull('suspended_until')
            ->where('suspended_until', '>', now());
    }

    public function scopeBanned($query)
    {
        return $query->onlyTrashed();
    }

    public function scopeWithStrikes($query, $minStrikes = 1)
    {
        return $query->where('strike_count', '>=', $minStrikes);
    }

    // Status Methods
    /**
     * Check if user is currently suspended
     */
    public function isSuspended(): bool
    {
        return $this->suspended_until && $this->suspended_until->isFuture();
    }

    /**
     * Check if user is banned (soft deleted)
     */
    public function isBanned(): bool
    {
        return $this->trashed();
    }

    /**
     * Activate the user
     */
    public function activate(): self
    {
        $this->update(['is_active' => true]);
        return $this;
    }

    /**
     * Deactivate the user
     */
    public function deactivate(): self
    {
        $this->update(['is_active' => false]);
        return $this;
    }

    /**
     * Toggle active status
     */
    public function toggleActive(): self
    {
        $this->update(['is_active' => !$this->is_active]);
        return $this;
    }

    public function isActive(): bool
    {
        return !$this->trashed() &&
            !$this->isSuspended() &&
            $this->is_active === true;
    }

    // Fraud Control Methods - SIMPLIFIED VERSION
    public function addStrike($reason = 'Policy violation')
    {
        $this->increment('strike_count');

        $flags = $this->fraud_flags ?? [];
        $flags[] = [
            'type' => 'strike',
            'reason' => $reason,
            'strike_count' => $this->strike_count,
            'created_at' => now()->toISOString()
        ];

        $this->update(['fraud_flags' => $flags]);

        return $this;
    }

    public function suspendUser($until, $reason = null)
    {
        // Ensure $until is a Carbon instance
        if (!$until instanceof Carbon) {
            if (is_numeric($until)) {
                // If it's a number, treat it as days
                $until = now()->addDays((int)$until);
            } else {
                // Try to parse it as a date string
                $until = Carbon::parse($until);
            }
        }

        $this->update([
            'suspended_until' => $until,
            'suspension_reason' => $reason
        ]);

        // Mark products as unavailable instead of deleting
        $this->products()->update(['is_available' => false]);

        return $this;
    }

    public function unsuspendUser()
    {
        $this->update([
            'suspended_until' => null,
            'suspension_reason' => null
        ]);

        return $this;
    }

    public function banUser($reason = null, $deletedBy = null)
    {
        $this->update([
            'deleted_reason' => $reason,
            'deleted_by' => $deletedBy
        ]);

        // Soft delete the user (keeps all records)
        $this->delete();

        // Mark products as unavailable
        $this->products()->update(['is_available' => false]);

        return $this;
    }

    public function restoreUser($restoredBy = null)
    {
        $this->restore();

        $this->update([
            'deleted_reason' => null,
            'deleted_by' => null,
            'strike_count' => 0,
            'fraud_flags' => null,
            'suspended_until' => null,
            'suspension_reason' => null
        ]);

        return $this;
    }

    public function addFraudFlag($type, $details, $reportedBy = null)
    {
        $flags = $this->fraud_flags ?? [];
        $flags[] = [
            'type' => $type,
            'details' => $details,
            'reported_by' => $reportedBy,
            'created_at' => now()->toISOString()
        ];

        $this->update(['fraud_flags' => $flags]);

        return $this;
    }

    public function getFraudScore()
    {
        $score = 0;
        $flags = $this->fraud_flags ?? [];

        foreach ($flags as $flag) {
            switch ($flag['type']) {
                case 'fake_product':
                    $score += 3;
                    break;
                case 'payment_issue':
                    $score += 2;
                    break;
                case 'harassment':
                    $score += 4;
                    break;
                case 'spam':
                    $score += 1;
                    break;
                case 'strike':
                    $score += 2;
                    break;
                default:
                    $score += 1;
            }
        }

        return $score + $this->strike_count;
    }

    public function getRiskLevel()
    {
        $score = $this->getFraudScore();

        if ($score >= 10) return 'critical';
        if ($score >= 7) return 'high';
        if ($score >= 4) return 'medium';
        if ($score >= 1) return 'low';

        return 'none';
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

    // Override default model methods to handle soft delete scenarios
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->withTrashed()
            ->firstOrFail();
    }


    /**
     * Get the user's activities
     */
    public function activities()
    {
        return $this->hasMany(UserActivity::class)->latest('performed_at');
    }

    /**
     * Get recent activities (last 30 days)
     */
    public function recentActivities($days = 30)
    {
        return $this->activities()->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Get login activities
     */
    public function loginActivities()
    {
        return $this->activities()->ofType('login');
    }

    /**
     * Get last login activity
     */
    public function lastLogin()
    {
        return $this->loginActivities()->latest()->first();
    }

    /**
     * Get last activity
     */
    public function lastActivity()
    {
        return $this->activities()->latest()->first();
    }
}
