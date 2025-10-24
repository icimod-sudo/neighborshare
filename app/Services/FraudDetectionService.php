<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Exchange;
use Illuminate\Support\Facades\Log;

class FraudDetectionService
{
    public function detectUserFraudPatterns(User $user): array
    {
        $patterns = [];
        
        // Pattern 1: Too many products in short time
        $recentProducts = $user->products()
            ->where('created_at', '>=', now()->subDays(2))
            ->count();
            
        if ($recentProducts > 5) {
            $patterns[] = [
                'type' => 'rapid_listing',
                'severity' => 'medium',
                'message' => "User listed {$recentProducts} products in 2 days"
            ];
        }
        
        // Pattern 2: High exchange cancellation rate
        $totalExchanges = $user->sentExchanges()->count();
        $cancelledExchanges = $user->sentExchanges()->where('status', 'cancelled')->count();
        
        if ($totalExchanges > 0 && ($cancelledExchanges / $totalExchanges) > 0.7) {
            $patterns[] = [
                'type' => 'high_cancellation_rate',
                'severity' => 'high',
                'message' => "User has {$cancelledExchanges} cancelled exchanges out of {$totalExchanges} total"
            ];
        }
        
        // Pattern 3: Multiple similar product titles
        $productTitles = $user->products()->pluck('title');
        $similarTitles = $this->findSimilarTitles($productTitles);
        
        if (count($similarTitles) > 2) {
            $patterns[] = [
                'type' => 'duplicate_listings',
                'severity' => 'medium',
                'message' => "User has multiple similar product titles"
            ];
        }
        
        return $patterns;
    }
    
    public function autoFlagSuspiciousActivity(User $user): void
    {
        $patterns = $this->detectUserFraudPatterns($user);
        
        foreach ($patterns as $pattern) {
            if ($pattern['severity'] === 'high') {
                $user->addFraudFlag(
                    'suspicious_behavior', 
                    "Auto-detected: {$pattern['message']}", 
                    auth()->id() ?? 1
                );
                
                Log::warning("Auto-flagged user {$user->id} for {$pattern['type']}");
            }
        }
    }
    
    private function findSimilarTitles($titles): array
    {
        // Implement similarity detection logic
        return [];
    }
}