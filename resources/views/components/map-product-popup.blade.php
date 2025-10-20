<div class="max-w-xs">
    @if($product->image)
    <img src="{{ asset('storage/' . $product->image) }}"
        alt="{{ $product->title }}"
        class="w-full h-20 object-cover rounded-t-lg mb-2">
    @else
    <div class="w-full h-20 bg-gray-200 rounded-t-lg flex items-center justify-center mb-2">
        <span class="text-gray-400">📦</span>
    </div>
    @endif

    <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ $product->title }}</h4>
    <p class="text-gray-600 text-xs mb-2">{{ $product->subcategory }}</p>

    <div class="flex justify-between items-center text-xs mb-2">
        @if($product->is_free)
        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">FREE</span>
        @else
        <span class="font-semibold text-blue-600">₹{{ $product->price }}</span>
        @endif
        <span class="text-gray-500">{{ $product->quantity }}{{ $product->unit }}</span>
    </div>

    <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
        <span>By: {{ $product->user->name }}</span>
        @if(isset($product->distance))
        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
            {{ $product->distance }}km
        </span>
        @endif
    </div>

    <a href="{{ route('products.show', $product) }}"
        class="block w-full bg-blue-500 text-white text-center py-1 rounded text-xs hover:bg-blue-600 transition-colors">
        View Details
    </a>
</div>