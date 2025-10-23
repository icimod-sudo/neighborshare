<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Exchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Define all possible categories
        $categories = ['vegetable', 'fruit', 'plants', 'dairy', 'fmcg', 'other'];

        // Build category counts with fallback to 0
        $categoryCounts = [];
        foreach ($categories as $category) {
            $categoryCounts[$category] = Product::where('is_available', true)
                ->where('category', $category)
                ->count();
        }

        // Calculate browse-focused statistics
        $stats = [
            // Total available products in the system
            'total_available_products' => Product::where('is_available', true)->count(),

            // Free products available
            'free_products' => Product::where('is_available', true)->where('is_free', true)->count(),

            // Category counts for quick browse
            'category_counts' => $categoryCounts,

            // User's own stats
            'total_products' => Product::where('user_id', $user->id)->count(),
            'available_products' => Product::where('user_id', $user->id)->where('is_available', true)->count(),
            'total_exchanges' => Exchange::where('from_user_id', $user->id)
                ->orWhere('to_user_id', $user->id)
                ->count(),
            'completed_exchanges' => Exchange::where(function ($query) use ($user) {
                $query->where('from_user_id', $user->id)
                    ->orWhere('to_user_id', $user->id);
            })->where('status', 'completed')->count(),
        ];

        // Calculate success rate
        $totalExchanges = $stats['total_exchanges'];
        $completedExchanges = $stats['completed_exchanges'];
        $stats['success_rate'] = $totalExchanges > 0 ? round(($completedExchanges / $totalExchanges) * 100) : 0;

        // Pending exchanges count for the alert
        $pendingExchangesCount = Exchange::where('to_user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Debug output (remove this in production)
        // \Log::info('Dashboard Stats', $stats);

        return view('dashboard', compact('stats', 'pendingExchangesCount'));
    }
}
