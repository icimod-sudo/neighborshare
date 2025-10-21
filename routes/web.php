<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\ProfileController;
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
    Route::get('/products/map', [ProductController::class, 'map'])->name('products.map'); // Add this line
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
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



    // Temporary debug route - remove after testing
    Route::get('/debug-map-data', function () {
        $user = Auth::user();

        echo "<h1>Map Data Debug</h1>";
        echo "<p><strong>User:</strong> {$user->name}</p>";
        echo "<p><strong>User Location:</strong> {$user->latitude}, {$user->longitude}</p>";

        $products = \App\Models\Product::available()->with('user')->get();
        echo "<p><strong>Total Available Products:</strong> {$products->count()}</p>";

        echo "<h2>Products with User Locations:</h2>";
        $productsWithLocation = $products->filter(function ($product) {
            return $product->user && $product->user->latitude && $product->user->longitude;
        });

        echo "<p><strong>Products with Location Data:</strong> {$productsWithLocation->count()}</p>";

        foreach ($productsWithLocation as $product) {
            echo "<p><strong>{$product->title}</strong> - User: {$product->user->name} - Location: {$product->user->latitude}, {$product->user->longitude}</p>";
        }

        // Test distance calculation
        if ($user->latitude && $user->longitude) {
            echo "<h2>Distance Calculation Test:</h2>";
            foreach ($productsWithLocation->take(3) as $product) {
                $distance = $product->getDistanceTo($user->latitude, $user->longitude);
                echo "<p><strong>{$product->title}</strong> - Distance: {$distance}km</p>";
            }
        }
    });
});

require __DIR__ . '/auth.php';
