<?php

namespace App\Http\Middleware;

use App\Services\ActivityTrackerService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track activity for authenticated users
        if (Auth::check() && $this->shouldTrack($request)) {
            $this->trackActivity($request);
        }

        return $response;
    }

    /**
     * Determine if the request should be tracked
     */
    private function shouldTrack(Request $request): bool
    {
        // Don't track these methods
        if (in_array($request->method(), ['OPTIONS', 'HEAD'])) {
            return false;
        }

        // Don't track these routes
        $excludedRoutes = [
            'admin.*',
            'livewire.*',
            'horizon.*',
            'telescope.*',
            'log-viewer.*',
        ];

        foreach ($excludedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Track the activity based on route and method
     */
    private function trackActivity(Request $request): void
    {
        $user = Auth::user();
        $routeName = $request->route()->getName();

        try {
            match ($routeName) {
                'products.show' => ActivityTrackerService::trackProductView(
                    $user,
                    $request->route('product')->id,
                    $request->route('product')->title
                ),
                'products.store' => ActivityTrackerService::trackProductCreate(
                    $user,
                    $request->route('product')?->id ?? 'new',
                    $request->input('title', 'New Product')
                ),
                'products.update' => ActivityTrackerService::trackProductUpdate(
                    $user,
                    $request->route('product')->id,
                    $request->route('product')->title
                ),
                'products.destroy' => ActivityTrackerService::trackProductDelete(
                    $user,
                    $request->route('product')->id,
                    $request->route('product')->title
                ),
                'exchanges.store' => ActivityTrackerService::trackExchangeRequest(
                    $user,
                    $request->route('exchange')?->id ?? 'new',
                    $request->route('product')->title
                ),
                'exchanges.accept' => ActivityTrackerService::trackExchangeAccept(
                    $user,
                    $request->route('exchange')->id
                ),
                'exchanges.complete' => ActivityTrackerService::trackExchangeComplete(
                    $user,
                    $request->route('exchange')->id
                ),
                'profile.update' => ActivityTrackerService::trackProfileUpdate($user),
                'profile.location.update' => ActivityTrackerService::trackLocationUpdate($user),
                default => null
            };
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::error('Activity tracking failed: ' . $e->getMessage());
        }
    }
}
