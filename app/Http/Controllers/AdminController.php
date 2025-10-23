<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Exchange;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_products' => Product::count(),
            'total_exchanges' => Exchange::count(),
            'pending_exchanges' => Exchange::where('status', 'pending')->count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_products_today' => Product::whereDate('created_at', today())->count(),
        ];

        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        $popularCategories = Product::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivities', 'popularCategories'));
    }

    public function users(Request $request): View
    {
        $query = User::withCount(['products', 'sentExchanges', 'receivedExchanges']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            match ($request->status) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function products(Request $request): View
    {
        $query = Product::with(['user', 'exchanges']);

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            match ($request->status) {
                'available' => $query->where('is_available', true),
                'unavailable' => $query->where('is_available', false),
                default => null,
            };
        }

        $products = $query->latest()->paginate(20);

        return view('admin.products', compact('products'));
    }

    public function exchanges(Request $request): View
    {
        $query = Exchange::with(['product', 'fromUser', 'toUser']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $exchanges = $query->latest()->paginate(20);

        return view('admin.exchanges', compact('exchanges'));
    }

    public function activityLogs(Request $request): View
    {
        $query = ActivityLog::with('user');

        if ($request->has('date')) {
            match ($request->date) {
                'today' => $query->today(),
                'week' => $query->thisWeek(),
                'month' => $query->thisMonth(),
                default => null,
            };
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        $activities = $query->latest()->paginate(50);

        return view('admin.activity-logs', compact('activities'));
    }

    public function toggleUserStatus(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'description' => $user->is_active ? 'Activated user: ' . $user->name : 'Deactivated user: ' . $user->name,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        return back()->with('success', 'User status updated successfully.');
    }

    public function deleteUser(User $user): RedirectResponse
    {
        $userName = $user->name;
        $user->delete();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'description' => 'Deleted user: ' . $userName,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        return back()->with('success', 'User deleted successfully.');
    }

    public function toggleProductStatus(Product $product): RedirectResponse
    {
        $product->update(['is_available' => !$product->is_available]);
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'description' => ($product->is_available ? 'Activated' : 'Deactivated') . ' product: ' . $product->title,
            'model_type' => Product::class,
            'model_id' => $product->id,
        ]);

        return back()->with('success', 'Product status updated successfully.');
    }

    public function deleteProduct(Product $product): RedirectResponse
    {
        $productTitle = $product->title;
        $product->delete();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'description' => 'Deleted product: ' . $productTitle,
            'model_type' => Product::class,
            'model_id' => $product->id,
        ]);

        return back()->with('success', 'Product deleted successfully.');
    }

    public function updateExchangeStatus(Request $request, Exchange $exchange): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,completed,cancelled'
        ]);

        $oldStatus = $exchange->status;
        $exchange->update(['status' => $request->status]);
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'description' => "Updated exchange status from {$oldStatus} to {$request->status}",
            'model_type' => Exchange::class,
            'model_id' => $exchange->id,
        ]);

        return back()->with('success', 'Exchange status updated successfully.');
    }
}
