<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});






Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Product Routes
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/map', [ProductController::class, 'map'])->name('products.map');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products/category/{category}', [ProductController::class, 'byCategory'])->name('products.category');
    Route::get('/my-products', [ProductController::class, 'myProducts'])->name('products.my');

    // Exchange Routes
    Route::post('/exchanges/{product}', [ExchangeController::class, 'store'])->name('exchanges.store');
    Route::get('/my-exchanges', [ExchangeController::class, 'myExchanges'])->name('exchanges.my');
    Route::get('/exchanges/{exchange}', [ExchangeController::class, 'show'])->name('exchanges.show');
    Route::patch('/exchanges/{exchange}/accept', [ExchangeController::class, 'accept'])->name('exchanges.accept');
    Route::patch('/exchanges/{exchange}/complete', [ExchangeController::class, 'complete'])->name('exchanges.complete');
    Route::patch('/exchanges/{exchange}/cancel', [ExchangeController::class, 'cancel'])->name('exchanges.cancel');

    // AJAX location update
    Route::post('/profile/location', [ProfileController::class, 'updateLocation'])->name('profile.location.update');


    // Exchange routes
    Route::get('/exchanges/count', [ExchangeController::class, 'getExchangesCount'])->name('exchanges.count');
    Route::get('/exchanges/my', [ExchangeController::class, 'myExchanges'])->name('exchanges.my');

    Route::get('/dashboard', function () {
        $user = Auth::user();

        // Calculate browse-focused statistics
        $stats = [
            // Total available products in the system
            'total_available_products' => \App\Models\Product::where('is_available', true)->count(),

            // Products near user (within 5km)
            'nearby_products' => 0, // Simplified for route closure
            'free_products' => \App\Models\Product::where('is_available', true)->where('is_free', true)->count(),

            // Category counts for quick browse
            'category_counts' => [
                'vegetable' => \App\Models\Product::where('is_available', true)->where('category', 'vegetable')->count(),
                'fruit' => \App\Models\Product::where('is_available', true)->where('category', 'fruit')->count(),
                'plants' => \App\Models\Product::where('is_available', true)->where('category', 'plants')->count(),
                'dairy' => \App\Models\Product::where('is_available', true)->where('category', 'dairy')->count(),
                'fmcg' => \App\Models\Product::where('is_available', true)->where('category', 'fmcg')->count(),
                'other' => \App\Models\Product::where('is_available', true)->where('category', 'other')->count(),
            ],

            // User's own stats
            'total_products' => \App\Models\Product::where('user_id', $user->id)->count(),
            'available_products' => \App\Models\Product::where('user_id', $user->id)->where('is_available', true)->count(),
            'total_exchanges' => \App\Models\Exchange::where('from_user_id', $user->id)
                ->orWhere('to_user_id', $user->id)
                ->count(),
            'completed_exchanges' => \App\Models\Exchange::where(function ($query) use ($user) {
                $query->where('from_user_id', $user->id)
                    ->orWhere('to_user_id', $user->id);
            })->where('status', 'completed')->count(),
        ];

        // Calculate success rate
        $totalExchanges = $stats['total_exchanges'];
        $completedExchanges = $stats['completed_exchanges'];
        $stats['success_rate'] = $totalExchanges > 0 ? round(($completedExchanges / $totalExchanges) * 100) : 0;

        // Get featured products (simplified for route closure)
        $featuredProducts = \App\Models\Product::where('is_available', true)
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'latitude', 'longitude');
            }])
            ->latest()
            ->limit(6)
            ->get();

        // Pending exchanges count for the alert
        $pendingExchangesCount = \App\Models\Exchange::where('to_user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return view('dashboard', compact('stats', 'featuredProducts', 'pendingExchangesCount'));
    })->middleware(['auth', 'verified'])->name('dashboard');


    // Admin Routes
    Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
        Route::get('/exchanges', [AdminController::class, 'exchanges'])->name('admin.exchanges');
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('admin.activity-logs');

        // User Management
        Route::post('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle-status');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

        // Product Management
        Route::post('/products/{product}/toggle-status', [AdminController::class, 'toggleProductStatus'])->name('admin.products.toggle-status');
        Route::delete('/products/{product}', [AdminController::class, 'deleteProduct'])->name('admin.products.delete');

        // Exchange Management
        Route::post('/exchanges/{exchange}/update-status', [AdminController::class, 'updateExchangeStatus'])->name('admin.exchanges.update-status');
    });

    Route::get('/offline', function () {
        return view('offline');
    });



});

require __DIR__ . '/auth.php';
