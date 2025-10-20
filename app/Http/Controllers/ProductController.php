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
            $radius = $request->radius ?? 2;

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
        // Start with base query
        $query = Product::available()->with(['user' => function ($query) {
            $query->select('id', 'name', 'latitude', 'longitude', 'neighborhood');
        }]);

        // Apply filters
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Get all products
        $products = $query->get();

        // Get user's location
        $user = Auth::user();
        $userLatitude = $user->latitude ?? null;
        $userLongitude = $user->longitude ?? null;

        // If user has location, filter by distance
        if ($userLatitude && $userLongitude) {
            $radius = $request->radius ?? 2;

            $products = $products->filter(function ($product) use ($userLatitude, $userLongitude, $radius) {
                $distance = $product->getDistanceTo($userLatitude, $userLongitude);

                if ($distance === null) {
                    return false;
                }

                $product->distance = $distance;
                return $distance <= $radius;
            })->sortBy('distance');
        }

        // Generate map markers
        $mapMarkers = $this->generateMapMarkers($products, $userLatitude, $userLongitude);

        return view('products.map', compact('products', 'userLatitude', 'userLongitude', 'mapMarkers'));
    }

    // Helper method to paginate a collection
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

    // Generate map markers for products
    private function generateMapMarkers($products, $userLat = null, $userLon = null)
    {
        $markers = [];

        // Add user location marker if available
        if ($userLat && $userLon) {
            $markers[] = [
                'lat' => $userLat,
                'lon' => $userLon,
                'title' => 'Your Location',
                'color' => 'blue',
                'icon' => 'user',
                'popup' => 'Your current location'
            ];
        }

        // Add product markers
        foreach ($products as $product) {
            if ($product->user && $product->user->latitude && $product->user->longitude) {
                $popupContent = view('components.map-product-popup', compact('product'))->render();

                $markers[] = [
                    'lat' => $product->user->latitude,
                    'lon' => $product->user->longitude,
                    'title' => $product->title,
                    'color' => $product->is_free ? 'green' : 'orange',
                    'icon' => 'product',
                    'popup' => $popupContent,
                    'product_id' => $product->id
                ];
            }
        }

        return $markers;
    }

    // ... keep other methods the same (create, store, show, etc.)
    public function create()
    {
        $categories = [
            'vegetable' => 'Vegetables',
            'fruit' => 'Fruits',
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
        ]);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product listed successfully!');
    }

    public function show(Product $product)
    {
        $product->load('user');
        return view('products.show', compact('product'));
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
            $radius = $request->radius ?? 2;

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
            'fmcg' => 'FMCG Products',
            'dairy' => 'Dairy',
            'other' => 'Other'
        ];

        return view('products.category', compact('products', 'category', 'categoryNames', 'userLatitude', 'userLongitude'));
    }
}
