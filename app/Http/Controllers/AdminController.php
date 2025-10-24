<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Exchange;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // DASHBOARD
    public function dashboard(): View
    {
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities();
        $highRiskUsers = $this->getHighRiskUsers();
        $fraudActivities = $this->getFraudActivities();

        // Add activity statistics
        $activityStats = [
            'total_activities_today' => UserActivity::whereDate('performed_at', today())->count(),
            'total_logins_today' => UserActivity::where('type', 'login')->whereDate('performed_at', today())->count(),
            'active_users_today' => UserActivity::whereDate('performed_at', today())->distinct('user_id')->count('user_id'),
            'product_activities_today' => UserActivity::where('type', 'like', 'product_%')->whereDate('performed_at', today())->count(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentActivities',
            'highRiskUsers',
            'fraudActivities',
            'activityStats'
        ));
    }

    // USER MANAGEMENT
    public function users(Request $request): View
    {
        $query = User::withCount(['products', 'sentExchanges', 'receivedExchanges'])
            ->with('deleter');

        $this->applyUserFilters($query, $request);

        $users = $query->latest()->paginate(8);
        $this->addFraudDataToUsers($users);

        return view('admin.users', compact('users'));
    }

    public function userDetail(User $user): View
    {
        // Load relationships with counts
        $user->load([
            'deleter',
            'products' => function ($query) {
                $query->withTrashed()->withCount('exchanges');
            },
            'sentExchanges',
            'receivedExchanges'
        ]);

        // Load products count separately to ensure it's available
        $user->loadCount([
            'products as active_products_count' => function ($query) {
                $query->where('is_available', true);
            },
            'sentExchanges as sent_exchanges_count',
            'receivedExchanges as received_exchanges_count'
        ]);

        // Get fraud data
        [$fraudScore, $riskLevel] = $this->getUserFraudData($user);

        // Get recent flags (last 10)
        $recentFlags = $user->fraud_flags ? array_slice($user->fraud_flags, -10) : [];

        // Ensure last_login_at is properly formatted
        if ($user->last_login_at && !$user->last_login_at instanceof \Carbon\Carbon) {
            $user->last_login_at = \Carbon\Carbon::parse($user->last_login_at);
        }

        return view('admin.user-detail', compact(
            'user',
            'fraudScore',
            'riskLevel',
            'recentFlags'
        ));
    }

    // FRAUD CONTROL METHODS
    public function suspendUser(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'suspension_days' => 'required|integer|min:1|max:365',
            'reason' => 'required|string|max:500'
        ]);

        $suspensionDays = (int) $request->suspension_days;
        $until = now()->addDays($suspensionDays);

        $this->performSuspension($user, $until, $request->reason);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'ip' => request()->ip(),
                'suspension_days' => $suspensionDays,
                'reason' => $request->reason,
                'suspended_until' => $until
            ])
            ->log("Admin suspended user {$user->name} until {$until->format('M j, Y')}: {$request->reason}");

        return back()->with('success', "User {$user->name} has been suspended until {$until->format('M j, Y')}.");
    }

    public function unsuspendUser(User $user): RedirectResponse
    {
        $this->performUnsuspension($user);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties(['ip' => request()->ip()])
            ->log("Admin unsuspended user: {$user->name}");

        return back()->with('success', "User {$user->name} has been unsuspended.");
    }

    public function banUser(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $this->performBan($user, $request->reason, Auth::id());

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'ip' => request()->ip(),
                'reason' => $request->reason
            ])
            ->log("Admin banned user {$user->name}: {$request->reason}");

        return back()->with('success', "User {$user->name} has been banned.");
    }

    public function restoreUser(User $user): RedirectResponse
    {
        $this->performRestore($user);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties(['ip' => request()->ip()])
            ->log("Admin restored banned user: {$user->name}");

        return back()->with('success', "User {$user->name} has been restored.");
    }

    public function addStrike(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $this->performAddStrike($user, $request->reason);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'ip' => request()->ip(),
                'reason' => $request->reason,
                'strike_count' => $user->strike_count
            ])
            ->log("Admin added strike to {$user->name}: {$request->reason} (Total: {$user->strike_count})");

        return back()->with('success', "Strike added to {$user->name}. Total strikes: {$user->strike_count}");
    }

    public function removeStrike(User $user): RedirectResponse
    {
        if ($user->strike_count > 0) {
            $user->decrement('strike_count');

            activity()
                ->causedBy(Auth::user())
                ->performedOn($user)
                ->withProperties([
                    'ip' => request()->ip(),
                    'strike_count' => $user->strike_count
                ])
                ->log("Admin removed strike from {$user->name} (Total: {$user->strike_count})");

            return back()->with('success', "Strike removed from {$user->name}. Total strikes: {$user->strike_count}");
        }

        return back()->with('error', "User {$user->name} has no strikes to remove.");
    }

    public function addFraudFlag(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:fake_product,payment_issue,harassment,spam,other',
            'details' => 'required|string|max:500'
        ]);

        $this->performAddFraudFlag($user, $request->type, $request->details, Auth::id());

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'ip' => request()->ip(),
                'type' => $request->type,
                'details' => $request->details
            ])
            ->log("Admin added fraud flag to {$user->name}: {$request->type} - {$request->details}");

        return back()->with('success', "Fraud flag added to {$user->name}.");
    }

    // DELETED DATA MANAGEMENT
    public function deletedUsers(Request $request): View
    {
        $query = User::onlyTrashed()
            ->withCount(['products', 'sentExchanges', 'receivedExchanges'])
            ->with('deleter');

        $this->applyDeletedUsersFilters($query, $request);

        $users = $query->latest('deleted_at')->paginate(20);
        return view('admin.deleted-users', compact('users'));
    }

    public function deletedProducts(Request $request): View
    {
        $query = Product::onlyTrashed()->with(['user' => function ($query) {
            $query->withTrashed();
        }]);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('deleted_reason', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('fraud_only')) {
            $query->where(function ($q) {
                $q->where('deleted_reason', 'like', '%fraud%')
                    ->orWhere('deleted_reason', 'like', '%fake%')
                    ->orWhere('deleted_reason', 'like', '%scam%')
                    ->orWhere('deleted_reason', 'like', '%spam%');
            });
        }

        $products = $query->latest('deleted_at')->paginate(20);

        // Statistics
        $stats = [
            'total_deleted' => Product::onlyTrashed()->count(),
            'fraud_related' => Product::onlyTrashed()->where(function ($q) {
                $q->where('deleted_reason', 'like', '%fraud%')
                    ->orWhere('deleted_reason', 'like', '%fake%')
                    ->orWhere('deleted_reason', 'like', '%scam%');
            })->count(),
            'deleted_today' => Product::onlyTrashed()->whereDate('deleted_at', today())->count(),
            'restored_week' => Product::whereNotNull('restored_at')
                ->where('restored_at', '>=', now()->subWeek())
                ->count(),
        ];

        return view('admin.deleted-products', compact('products', 'stats'));
    }

    public function restoreProduct($product): RedirectResponse
    {
        try {
            // If $product is an ID, find the product
            if (is_numeric($product)) {
                $product = Product::withTrashed()->findOrFail($product);
            }

            // If it's already a Product model, ensure it's loaded with trashed
            if ($product instanceof Product && !$product->exists) {
                $product = Product::withTrashed()->findOrFail($product->id);
            }

            \Log::info('Restore product attempt', [
                'product_id' => $product->id,
                'product_title' => $product->title,
                'is_trashed' => $product->trashed(),
                'admin_id' => Auth::id()
            ]);

            if (!$product->trashed()) {
                return back()->with('error', 'Product is not deleted.');
            }

            $productTitle = $product->title;

            // Restore using the model's restore method
            $product->restore();

            // Update additional fields
            $product->update([
                'is_available' => true,
                'deleted_reason' => null,
                'restored_at' => now(),
                'restored_by' => Auth::id()
            ]);

            \Log::info('Product restored successfully', [
                'product_id' => $product->id,
                'product_title' => $productTitle
            ]);

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($product)
                ->withProperties([
                    'ip' => request()->ip(),
                    'previous_deletion_reason' => $product->getOriginal('deleted_reason')
                ])
                ->log("Admin restored product: {$productTitle}");

            return back()->with('success', "Product '{$productTitle}' has been restored successfully.");
        } catch (\Exception $e) {
            \Log::error('Error restoring product: ' . $e->getMessage(), [
                'product' => $product,
                'admin_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to restore product. Please try again.');
        }
    }

    public function forceDeleteProduct(Product $product): RedirectResponse
    {
        try {
            if (!$product->trashed()) {
                return back()->with('error', 'Product is not deleted. Use regular delete instead.');
            }

            $productTitle = $product->title;
            $productId = $product->id;

            // Log before permanent deletion
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'product_title' => $productTitle,
                    'deletion_reason' => $product->deleted_reason,
                    'original_owner' => $product->user->name
                ])
                ->log("Admin permanently deleted product: {$productTitle}");

            // Permanent deletion
            $product->forceDelete();

            return back()->with('success', "Product '{$productTitle}' has been permanently deleted.");
        } catch (\Exception $e) {
            \Log::error('Error force deleting product: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'admin_id' => Auth::id()
            ]);

            return back()->with('error', 'Failed to permanently delete product. Please try again.');
        }
    }

    public function getProductDetails(Product $product)
    {
        try {
            // Load relationships
            $product->load(['user', 'exchanges']);

            $html = view('admin.partials.product-details', compact('product'))->render();

            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            \Log::error('Error loading product details: ' . $e->getMessage());

            return response()->json([
                'html' => '<div class="text-center text-red-600 py-4">Error loading product details</div>'
            ], 500);
        }
    }

    // BULK ACTIONS
    public function bulkUserAction(Request $request): RedirectResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:suspend_7,suspend_30,ban,add_strike,restore'
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();

        DB::transaction(function () use ($users, $request) {
            foreach ($users as $user) {
                $this->performBulkAction($user, $request->action);
            }
        });

        // Log bulk action
        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'ip' => request()->ip(),
                'action' => $request->action,
                'user_count' => $users->count()
            ])
            ->log("Admin performed bulk action '{$request->action}' on {$users->count()} users");

        return back()->with('success', "Bulk action completed for {$users->count()} users.");
    }

    // ACTIVITY & REPORTS
    public function activityLogs(Request $request): View
    {
        $query = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])->latest();

        $this->applyActivityLogsFilters($query, $request);

        $activities = $query->paginate(50);
        return view('admin.activity-logs', compact('activities'));
    }

    public function userActivities(Request $request): View
    {
        $query = UserActivity::with('user')->latest('performed_at');

        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('device_type') && $request->device_type) {
            $query->where('device_type', $request->device_type);
        }

        // Date range filters
        if ($request->has('date_range')) {
            match ($request->date_range) {
                'today' => $query->whereDate('performed_at', today()),
                'yesterday' => $query->whereDate('performed_at', today()->subDay()),
                'week' => $query->whereBetween('performed_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereBetween('performed_at', [now()->startOfMonth(), now()->endOfMonth()]),
                default => null,
            };
        }

        $activities = $query->paginate(50);

        // Statistics
        $totalActivities = UserActivity::count();
        $activeUsersCount = User::active()->count();
        $mobileActivities = UserActivity::where('device_type', 'mobile')->count();
        $productActivities = UserActivity::where('type', 'like', 'product_%')->count();

        $users = User::select('id', 'name', 'email')->get();

        return view('admin.user-activities', compact(
            'activities',
            'users',
            'totalActivities',
            'activeUsersCount',
            'mobileActivities',
            'productActivities'
        ));
    }

    public function highRiskUsers(Request $request): View
    {
        $query = User::withCount(['products', 'sentExchanges', 'receivedExchanges'])
            ->withStrikes(2)
            ->active();

        if ($request->has('min_score')) {
            $users = $query->get()->filter(function ($user) use ($request) {
                return $user->getFraudScore() >= $request->min_score;
            });
        } else {
            $users = $query->orderBy('strike_count', 'desc')->paginate(20);
            $this->addFraudDataToUsers($users);
        }

        return view('admin.high-risk-users', compact('users'));
    }

    public function fraudReports(Request $request): View
    {
        $weeklyReport = $this->getWeeklyFraudReport();
        $fraudCategories = $this->getFraudCategories();

        return view('admin.fraud-reports', compact('weeklyReport', 'fraudCategories'));
    }

    // PRODUCTS & EXCHANGES
    public function products(Request $request): View
    {
        $query = Product::with(['user', 'exchanges']);

        $this->applyProductsFilters($query, $request);

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

    // QUICK ACTIONS
    public function toggleUserStatus(User $user): RedirectResponse
    {
        try {
            $oldStatus = $user->is_active;
            $user->toggleActive();
            $newStatus = $user->is_active;

            // Log the action
            activity()
                ->causedBy(Auth::user())
                ->performedOn($user)
                ->withProperties([
                    'ip' => request()->ip(),
                    'old_status' => $oldStatus ? 'active' : 'inactive',
                    'new_status' => $newStatus ? 'active' : 'inactive'
                ])
                ->log("Admin " . ($newStatus ? 'activated' : 'deactivated') . " user: {$user->name}");

            $message = $newStatus
                ? "User {$user->name} has been activated successfully."
                : "User {$user->name} has been deactivated successfully.";

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error toggling user status: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'admin_id' => Auth::id()
            ]);

            return back()->with('error', 'Failed to update user status. Please try again.');
        }
    }

    public function toggleProductStatus(Product $product): RedirectResponse
    {
        $product->update(['is_available' => !$product->is_available]);

        $action = $product->is_available ? 'Activated' : 'Deactivated';

        activity()
            ->causedBy(Auth::user())
            ->performedOn($product)
            ->withProperties([
                'ip' => request()->ip(),
                'new_status' => $product->is_available ? 'available' : 'unavailable'
            ])
            ->log("Admin {$action} product: {$product->title}");

        return back()->with('success', 'Product status updated successfully.');
    }

    public function updateExchangeStatus(Request $request, Exchange $exchange): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,completed,cancelled'
        ]);

        $oldStatus = $exchange->status;
        $exchange->update(['status' => $request->status]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($exchange)
            ->withProperties([
                'ip' => request()->ip(),
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ])
            ->log("Admin updated exchange status from {$oldStatus} to {$request->status}");

        return back()->with('success', 'Exchange status updated successfully.');
    }

    // PRIVATE HELPER METHODS
    private function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'suspended_users' => User::suspended()->count(),
            'banned_users' => User::banned()->count(),
            'high_risk_users' => User::withStrikes(3)->active()->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('is_available', true)->count(),
            'deleted_products' => Product::onlyTrashed()->count(),
            'fraud_products' => Product::onlyTrashed()->where('deleted_reason', 'like', '%fraud%')->count(),
            'total_exchanges' => Exchange::count(),
            'pending_exchanges' => Exchange::where('status', 'pending')->count(),
            'deleted_exchanges' => Exchange::onlyTrashed()->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'fraud_flags_today' => \Spatie\Activitylog\Models\Activity::where('description', 'like', '%fraud%')
                ->whereDate('created_at', today())
                ->count(),
        ];
    }

    private function getRecentActivities()
    {
        return \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])
            ->latest()
            ->take(10)
            ->get();
    }

    private function getFraudActivities()
    {
        return \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])
            ->where('description', 'like', '%fraud%')
            ->orWhere('description', 'like', '%strike%')
            ->orWhere('description', 'like', '%suspend%')
            ->orWhere('description', 'like', '%ban%')
            ->latest()
            ->take(10)
            ->get();
    }

    private function getHighRiskUsers()
    {
        return User::withCount(['products', 'sentExchanges', 'receivedExchanges'])
            ->withStrikes(2)
            ->active()
            ->orderBy('strike_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($user) {
                [$user->fraud_score, $user->risk_level] = $this->getUserFraudData($user);
                return $user;
            });
    }

    private function getUserFraudData(User $user): array
    {
        if (method_exists($user, 'getFraudScore')) {
            return [$user->getFraudScore(), $user->getRiskLevel()];
        }
        return [0, 'none'];
    }

    private function addFraudDataToUsers($users): void
    {
        $users->getCollection()->transform(function ($user) {
            [$user->fraud_score, $user->risk_level] = $this->getUserFraudData($user);
            return $user;
        });
    }

    // FILTER METHODS
    private function applyUserFilters($query, Request $request): void
    {
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            match ($request->status) {
                'active' => $query->active(),
                'suspended' => $query->suspended(),
                'banned' => $query->banned(),
                'high_risk' => $query->withStrikes(3)->active(),
                'with_strikes' => $query->withStrikes(1)->active(),
                default => null,
            };
        }
    }

    private function applyDeletedUsersFilters($query, Request $request): void
    {
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('deleted_reason', 'like', '%' . $request->search . '%');
        }
    }

    private function applyActivityLogsFilters($query, Request $request): void
    {
        if ($request->has('date')) {
            match ($request->date) {
                'today' => $query->whereDate('created_at', today()),
                'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('created_at', now()->month),
                default => null,
            };
        }

        if ($request->has('action')) {
            $query->where('event', $request->action);
        }

        if ($request->has('fraud_only')) {
            $query->where('description', 'like', '%fraud%')
                ->orWhere('description', 'like', '%strike%')
                ->orWhere('description', 'like', '%suspend%')
                ->orWhere('description', 'like', '%ban%');
        }
    }

    private function applyProductsFilters($query, Request $request): void
    {
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
    }

    // ACTION METHODS
    private function performSuspension(User $user, $until, string $reason): void
    {
        if (method_exists($user, 'suspendUser')) {
            $user->suspendUser($until, $reason);
        } else {
            $user->update([
                'suspended_until' => $until,
                'suspension_reason' => $reason
            ]);
            $user->products()->update(['is_available' => false]);
        }
    }

    private function performUnsuspension(User $user): void
    {
        if (method_exists($user, 'unsuspendUser')) {
            $user->unsuspendUser();
        } else {
            $user->update([
                'suspended_until' => null,
                'suspension_reason' => null
            ]);
        }
    }

    private function performBan(User $user, string $reason, int $bannedBy): void
    {
        if (method_exists($user, 'banUser')) {
            $user->banUser($reason, $bannedBy);
        } else {
            $user->update([
                'deleted_reason' => $reason,
                'deleted_by' => $bannedBy
            ]);
            $user->delete();
            $user->products()->update(['is_available' => false]);
        }
    }

    private function performRestore(User $user): void
    {
        if (method_exists($user, 'restoreUser')) {
            $user->restoreUser(Auth::id());
        } else {
            $user->restore();
            $user->update([
                'deleted_reason' => null,
                'deleted_by' => null,
                'strike_count' => 0,
                'fraud_flags' => null,
            ]);
        }
    }

    private function performAddStrike(User $user, string $reason): void
    {
        if (method_exists($user, 'addStrike')) {
            $user->addStrike($reason);
        } else {
            $user->increment('strike_count');
            $flags = $user->fraud_flags ?? [];
            $flags[] = [
                'type' => 'strike',
                'reason' => $reason,
                'strike_count' => $user->strike_count,
                'created_at' => now()->toISOString()
            ];
            $user->update(['fraud_flags' => $flags]);
        }
    }

    private function performAddFraudFlag(User $user, string $type, string $details, int $reportedBy): void
    {
        if (method_exists($user, 'addFraudFlag')) {
            $user->addFraudFlag($type, $details, $reportedBy);
        } else {
            $flags = $user->fraud_flags ?? [];
            $flags[] = [
                'type' => $type,
                'details' => $details,
                'reported_by' => $reportedBy,
                'created_at' => now()->toISOString()
            ];
            $user->update(['fraud_flags' => $flags]);
        }
    }

    private function performBulkAction(User $user, string $action): void
    {
        switch ($action) {
            case 'suspend_7':
                $this->performSuspension($user, now()->addDays(7), 'Bulk action - 7 day suspension');
                break;
            case 'suspend_30':
                $this->performSuspension($user, now()->addDays(30), 'Bulk action - 30 day suspension');
                break;
            case 'ban':
                $this->performBan($user, 'Bulk action - banned', Auth::id());
                break;
            case 'add_strike':
                $this->performAddStrike($user, 'Bulk action - strike added');
                break;
            case 'restore':
                if ($user->trashed()) {
                    $this->performRestore($user);
                }
                break;
        }
    }

    // REPORTING METHODS
    private function getWeeklyFraudReport(): array
    {
        return [
            'new_fraud_cases' => \Spatie\Activitylog\Models\Activity::where('description', 'like', '%fraud%')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'users_suspended' => User::whereNotNull('suspended_until')
                ->where('suspended_until', '>', now())
                ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'users_banned' => User::onlyTrashed()
                ->whereBetween('deleted_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'strikes_issued' => \Spatie\Activitylog\Models\Activity::where('description', 'like', '%strike%')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];
    }

    private function getFraudCategories()
    {
        return Product::onlyTrashed()
            ->where('deleted_reason', 'like', '%fraud%')
            ->orWhere('deleted_reason', 'like', '%fake%')
            ->orWhere('deleted_reason', 'like', '%scam%')
            ->select('deleted_reason', DB::raw('count(*) as count'))
            ->groupBy('deleted_reason')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();
    }

    public function forceDeleteUser(User $user): RedirectResponse
    {
        try {
            if (!$user->trashed()) {
                return back()->with('error', 'User is not deleted. Use regular delete instead.');
            }

            $userName = $user->name;
            $userId = $user->id;

            // Log before permanent deletion
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_name' => $userName,
                    'user_email' => $user->email,
                    'deletion_reason' => $user->deleted_reason
                ])
                ->log("Admin permanently deleted user: {$userName}");

            // Permanent deletion
            $user->forceDelete();

            return back()->with('success', "User '{$userName}' has been permanently deleted.");
        } catch (\Exception $e) {
            \Log::error('Error force deleting user: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'admin_id' => Auth::id()
            ]);

            return back()->with('error', 'Failed to permanently delete user. Please try again.');
        }
    }
}
