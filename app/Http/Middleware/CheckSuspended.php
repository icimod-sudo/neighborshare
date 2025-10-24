<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        // If user is not authenticated, continue
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Check if user is inactive
        if (!$user->is_active && !$user->is_admin) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        // Check if user is suspended
        if ($user->isSuspended()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your account has been suspended until ' .
                    $user->suspended_until->format('M j, Y') .
                    '. Reason: ' . ($user->suspension_reason ?? 'No reason provided'));
        }

        // Check if user is banned
        if ($user->isBanned()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your account has been banned. Reason: ' .
                    ($user->deleted_reason ?? 'No reason provided'));
        }

        return $next($request);
    }
}
