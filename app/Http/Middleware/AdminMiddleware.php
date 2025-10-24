<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if admin user is banned
        if ($user->isBanned()) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your administrator account has been banned. Please contact system administrator.'
            ]);
        }

        // Check if admin user is suspended
        if ($user->isSuspended()) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your administrator account is suspended until ' .
                    $user->suspended_until->format('M j, Y') . '. ' .
                    'Reason: ' . ($user->suspension_reason ?? 'Policy violation')
            ]);
        }

        // Check if user has high fraud risk (prevent compromised admin access)
        if (method_exists($user, 'getFraudScore') && $user->getFraudScore() >= 8) {
            // Log suspicious admin access attempt using Laravel Log
            Log::warning("High-risk admin access attempt blocked", [
                'user_id' => $user->id,
                'email' => $user->email,
                'fraud_score' => $user->getFraudScore(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Alternative: Log using your ActivityLog model if it exists
            $this->logSecurityEvent($user, 'high_risk_admin_access_blocked', [
                'fraud_score' => $user->getFraudScore(),
                'ip' => $request->ip()
            ]);

            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Administrator access temporarily restricted due to security concerns. Please contact system administrator.'
            ]);
        }

        // Check if user is admin
        if (!$user->is_admin) {
            // Log unauthorized access attempt
            Log::warning("Unauthorized admin access attempt", [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            // Alternative: Log using your ActivityLog model
            $this->logSecurityEvent($user, 'unauthorized_admin_access', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            abort(403, 'Unauthorized access. Administrator privileges required.');
        }

        // Log successful admin access for audit trail
        if ($request->is('admin/*')) {
            Log::info("Admin panel access", [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            // Alternative: Log using your ActivityLog model
            $this->logSecurityEvent($user, 'admin_panel_access', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
        }

        return $next($request);
    }

    /**
     * Log security events using your ActivityLog model
     */
    private function logSecurityEvent($user, $eventType, $properties = [])
    {
        // Check if ActivityLog model exists and has the required method
        if (class_exists(ActivityLog::class) && method_exists(ActivityLog::class, 'create')) {
            try {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'security_event',
                    'description' => $this->getEventDescription($eventType, $properties),
                    'model_type' => User::class,
                    'model_id' => $user->id,
                    'type' => 'security',
                    'ip_address' => $properties['ip'] ?? request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } catch (\Exception $e) {
                // Fallback to Laravel log if ActivityLog fails
                Log::error("Failed to log security event: " . $e->getMessage());
            }
        }
    }

    /**
     * Get description for security events
     */
    private function getEventDescription($eventType, $properties = [])
    {
        $descriptions = [
            'high_risk_admin_access_blocked' => 'High-risk admin access attempt blocked - Fraud Score: ' . ($properties['fraud_score'] ?? 'N/A'),
            'unauthorized_admin_access' => 'Unauthorized admin access attempt from user',
            'admin_panel_access' => 'Admin panel access',
        ];

        return $descriptions[$eventType] ?? 'Security event: ' . $eventType;
    }
}
