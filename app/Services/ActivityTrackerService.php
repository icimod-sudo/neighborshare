<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityTrackerService
{
    /**
     * Track user activity
     */
    public static function track(string $type, string $description, array $metadata = [], User $user = null): UserActivity
    {
        $user = $user ?? Auth::user();
        $request = request();

        if (!$user) {
            throw new \Exception('No user provided or authenticated for activity tracking');
        }

        $deviceInfo = self::getDeviceInfo($request);

        return UserActivity::create([
            'user_id' => $user->id,
            'type' => $type,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'metadata' => $metadata,
            'performed_at' => now(),
        ]);
    }

    /**
     * Track user login
     */
    public static function trackLogin(User $user): UserActivity
    {
        return self::track(
            'login',
            "User logged in from " . request()->ip(),
            ['login_method' => 'email'] // Could be social, etc.
        );
    }

    /**
     * Track user logout
     */
    public static function trackLogout(User $user): UserActivity
    {
        return self::track(
            'logout',
            "User logged out",
            []
        );
    }

    /**
     * Track product view
     */
    public static function trackProductView(User $user, $productId, $productTitle): UserActivity
    {
        return self::track(
            'product_view',
            "Viewed product: {$productTitle}",
            ['product_id' => $productId, 'product_title' => $productTitle]
        );
    }

    /**
     * Track product creation
     */
    public static function trackProductCreate(User $user, $productId, $productTitle): UserActivity
    {
        return self::track(
            'product_create',
            "Created new product: {$productTitle}",
            ['product_id' => $productId, 'product_title' => $productTitle]
        );
    }

    /**
     * Track product update
     */
    public static function trackProductUpdate(User $user, $productId, $productTitle): UserActivity
    {
        return self::track(
            'product_update',
            "Updated product: {$productTitle}",
            ['product_id' => $productId, 'product_title' => $productTitle]
        );
    }

    /**
     * Track product deletion
     */
    public static function trackProductDelete(User $user, $productId, $productTitle): UserActivity
    {
        return self::track(
            'product_delete',
            "Deleted product: {$productTitle}",
            ['product_id' => $productId, 'product_title' => $productTitle]
        );
    }

    /**
     * Track exchange request
     */
    public static function trackExchangeRequest(User $user, $exchangeId, $productTitle): UserActivity
    {
        return self::track(
            'exchange_request',
            "Requested exchange for: {$productTitle}",
            ['exchange_id' => $exchangeId, 'product_title' => $productTitle]
        );
    }

    /**
     * Track exchange acceptance
     */
    public static function trackExchangeAccept(User $user, $exchangeId): UserActivity
    {
        return self::track(
            'exchange_accept',
            "Accepted exchange request #{$exchangeId}",
            ['exchange_id' => $exchangeId]
        );
    }

    /**
     * Track exchange completion
     */
    public static function trackExchangeComplete(User $user, $exchangeId): UserActivity
    {
        return self::track(
            'exchange_complete',
            "Completed exchange #{$exchangeId}",
            ['exchange_id' => $exchangeId]
        );
    }

    /**
     * Track profile update
     */
    public static function trackProfileUpdate(User $user): UserActivity
    {
        return self::track(
            'profile_update',
            "Updated profile information",
            []
        );
    }

    /**
     * Track location update
     */
    public static function trackLocationUpdate(User $user): UserActivity
    {
        return self::track(
            'location_update',
            "Updated location coordinates",
            ['latitude' => $user->latitude, 'longitude' => $user->longitude]
        );
    }

    /**
     * Get device information from request
     */
    private static function getDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent();

        // Simple device detection
        $deviceType = 'desktop';
        if (preg_match('/(android|webos|iphone|ipad|ipod|blackberry|windows phone)/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/(tablet|ipad|playbook|silk)/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        // Browser detection
        $browser = 'Unknown';
        if (preg_match('/chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/edge/i', $userAgent)) {
            $browser = 'Edge';
        }

        // Platform detection
        $platform = 'Unknown';
        if (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad/i', $userAgent)) {
            $platform = 'iOS';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    /**
     * Get user activity statistics
     */
    public static function getUserStats(User $user, int $days = 30): array
    {
        $activities = $user->activities()->recent($days)->get();

        return [
            'total_activities' => $activities->count(),
            'login_count' => $activities->where('type', 'login')->count(),
            'product_activities' => $activities->whereIn('type', ['product_view', 'product_create', 'product_update', 'product_delete'])->count(),
            'exchange_activities' => $activities->whereIn('type', ['exchange_request', 'exchange_accept', 'exchange_complete', 'exchange_cancel'])->count(),
            'recent_activity_count' => $activities->where('performed_at', '>=', now()->subDays(7))->count(),
            'most_active_day' => self::getMostActiveDay($activities),
            'preferred_device' => self::getPreferredDevice($activities),
        ];
    }

    private static function getMostActiveDay($activities): string
    {
        if ($activities->isEmpty()) return 'No activity';

        $dayCounts = $activities->groupBy(function ($activity) {
            return $activity->performed_at->format('l');
        })->map->count();

        return $dayCounts->sortDesc()->keys()->first() ?? 'No activity';
    }

    private static function getPreferredDevice($activities): string
    {
        if ($activities->isEmpty()) return 'Unknown';

        $deviceCounts = $activities->groupBy('device_type')->map->count();
        return $deviceCounts->sortDesc()->keys()->first() ?? 'Unknown';
    }
}
