<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Start with base query
        $query = Product::available()->with(['user' => function ($query) {
            $query->select('id', 'name', 'latitude', 'longitude', 'neighborhood');
        }]);

        // Apply filters that don't involve location
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('free_only') && $request->free_only) {
            $query->where('is_free', true);
        }

        // Get all filtered products first
        $products = $query->latest()->get();

        // Get user's location
        $user = Auth::user();
        $userLatitude = $user->latitude ?? null;
        $userLongitude = $user->longitude ?? null;

        // If user has location, filter by distance in PHP
        if ($userLatitude && $userLongitude) {
            $radius = $request->radius ?? 2; // Default to 2km if not specified

            $products = $products->filter(function ($product) use ($userLatitude, $userLongitude, $radius) {
                $distance = $product->getDistanceTo($userLatitude, $userLongitude);

                if ($distance === null) {
                    return false; // Skip products without location
                }

                // Add distance to product for display
                $product->distance = $distance;

                return $distance <= $radius;
            })->sortBy('distance');

            // Convert back to paginated collection for the view
            $products = $this->paginateCollection($products, 12);
        } else {
            // If no location, just paginate normally
            $products = $this->paginateCollection($products, 12);
        }

        return view('products.index', compact('products', 'userLatitude', 'userLongitude'));
    }

    public function map(Request $request)
    {
        $user = Auth::user();
        $userLatitude = $user->latitude ?? null;
        $userLongitude = $user->longitude ?? null;

        $radius = $request->get('radius', 2); // Get radius from request or default to 2km

        // Start with base query
        $query = Product::available()->with(['user' => function ($query) {
            $query->select('id', 'name', 'latitude', 'longitude', 'neighborhood');
        }]);

        // Get all products first
        $products = $query->get();

        // If user has location, filter by distance
        if ($userLatitude && $userLongitude) {
            $products = $products->filter(function ($product) use ($userLatitude, $userLongitude, $radius) {
                $distance = $product->getDistanceTo($userLatitude, $userLongitude);

                if ($distance === null) {
                    return false; // Skip products without location
                }

                // Add distance to product for display
                $product->distance = $distance;

                return $distance <= $radius;
            })->sortBy('distance');
        } else {
            // If no user location, show all products
            $products = $products->map(function ($product) {
                $product->distance = null;
                return $product;
            });
        }

        // Prepare map markers
        $mapMarkers = [];
        foreach ($products as $product) {
            if ($product->user->latitude && $product->user->longitude) {
                $mapMarkers[] = [
                    'lat' => $product->user->latitude,
                    'lon' => $product->user->longitude,
                    'popup' => $this->getMapPopupContent($product),
                    'icon' => 'product',
                    'color' => $product->is_free ? 'green' : 'orange'
                ];
            }
        }

        return view('products.map', [
            'products' => $products,
            'mapMarkers' => $mapMarkers,
            'userLatitude' => $userLatitude,
            'userLongitude' => $userLongitude,
            'radius' => $radius
        ]);
    }

    public function create()
    {
        $categories = [
            'vegetable' => 'Vegetables',
            'fruit' => 'Fruits',
            'plants' => 'Plants',
            'fmcg' => 'FMCG Products',
            'dairy' => 'Dairy',
            'other' => 'Other'
        ];

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'subcategory' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.1',
            'unit' => 'required|string|max:50',
            'condition' => 'required|in:fresh,good,average,expiring_soon',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'condition' => $request->condition,
            'price' => $request->is_free ? null : $request->price,
            'is_free' => $request->is_free ?? false,
            'image' => $imagePath,
            'expiry_date' => $request->expiry_date,
            'is_available' => true, // Use the existing column
        ]);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product listed successfully!');
    }

    public function show(Product $product)
    {
        $product->load('user');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        // Authorization check - user can only edit their own products
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $categories = [
            'vegetable' => 'Vegetables',
            'fruit' => 'Fruits',
            'plants' => 'Plants',
            'fmcg' => 'FMCG Products',
            'dairy' => 'Dairy',
            'other' => 'Other'
        ];

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        // Authorization check - user can only update their own products
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'subcategory' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.1',
            'unit' => 'required|string|max:50',
            'condition' => 'required|in:fresh,good,average,expiring_soon',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'expiry_date' => 'nullable|date|after:today',
            'is_available' => 'boolean', // Use the existing column
        ]);

        // Handle image upload
        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'condition' => $request->condition,
            'price' => $request->is_free ? null : $request->price,
            'is_free' => $request->is_free ?? false,
            'image' => $imagePath,
            'expiry_date' => $request->expiry_date,
            'is_available' => $request->has('is_available') ? $request->is_available : $product->is_available,
        ]);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // Authorization check - user can only delete their own products
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully!');
    }

    public function myProducts()
    {
        $products = Product::where('user_id', Auth::id())->latest()->paginate(10);
        return view('products.my', compact('products'));
    }

    public function byCategory($category, Request $request)
    {
        // Start with base query
        $query = Product::available()
            ->category($category)
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'latitude', 'longitude', 'neighborhood');
            }]);

        // Get all products for this category
        $products = $query->latest()->get();

        // Get user's location
        $user = Auth::user();
        $userLatitude = $user->latitude ?? null;
        $userLongitude = $user->longitude ?? null;

        // If user has location, filter by distance
        if ($userLatitude && $userLongitude) {
            $radius = $request->radius ?? 2; // Use requested radius or default to 2km

            $products = $products->filter(function ($product) use ($userLatitude, $userLongitude, $radius) {
                $distance = $product->getDistanceTo($userLatitude, $userLongitude);

                if ($distance === null) {
                    return false;
                }

                $product->distance = $distance;
                return $distance <= $radius;
            })->sortBy('distance');

            // Paginate the filtered collection
            $products = $this->paginateCollection($products, 12);
        } else {
            // If no location, just paginate normally
            $products = $this->paginateCollection($products, 12);
        }

        $categoryNames = [
            'vegetable' => 'Vegetables',
            'fruit' => 'Fruits',
            'plants' => 'Plants',
            'fmcg' => 'FMCG Products',
            'dairy' => 'Dairy',
            'other' => 'Other'
        ];

        return view('products.category', compact('products', 'category', 'categoryNames', 'userLatitude', 'userLongitude'));
    }

    /**
     * Generate popup content for map markers
     */
    private function getMapPopupContent($product)
    {
        $image = $product->image ?
            '<img src="' . asset('storage/' . $product->image) . '" alt="' . $product->title . '" class="w-full h-32 object-cover rounded-t-lg mb-2">' :
            '<div class="w-full h-32 bg-gray-200 rounded-t-lg flex items-center justify-center mb-2"><span class="text-gray-400 text-2xl">📦</span></div>';

        $price = $product->is_free ?
            '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">FREE</span>' :
            '<span class="font-semibold text-blue-600">₹' . $product->price . '</span>';

        $distanceInfo = '';
        if (isset($product->distance)) {
            $distanceInfo = '<div class="text-xs text-gray-500 mb-2">📍 ' . number_format($product->distance, 1) . 'km away</div>';
        }

        return '
        <div class="w-64">
            ' . $image . '
            <div class="p-3">
                <h4 class="font-semibold text-gray-900 mb-1">' . e($product->title) . '</h4>
                <p class="text-gray-600 text-sm mb-2">' . e($product->subcategory) . '</p>
                
                <div class="flex justify-between items-center text-sm mb-2">
                    ' . $price . '
                    <span class="text-gray-500">' . $product->quantity . $product->unit . '</span>
                </div>
                
                ' . $distanceInfo . '
                
                <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
                    <span>By: ' . e($product->user->name) . '</span>
                </div>
                
                <a href="' . route('products.show', $product) . '" 
                   class="block w-full bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 text-sm">
                    View Details
                </a>
            </div>
        </div>
    ';
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Helper method to paginate a collection
     */
    private function paginateCollection($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (\Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof \Illuminate\Support\Collection ? $items : \Illuminate\Support\Collection::make($items);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            $options
        );
    }
}
