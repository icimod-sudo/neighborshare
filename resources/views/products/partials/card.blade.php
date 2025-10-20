<div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
    @if($product->image)
    <img src="{{ asset('storage/' . $product->image) }}"
        alt="{{ $product->title }}"
        class="w-full h-40 object-cover rounded-t-lg">
    @else
    <div class="w-full h-40 bg-gray-200 rounded-t-lg flex items-center justify-center">
        <span class="text-gray-400 text-2xl">📦</span>
    </div>
    @endif

    <div class="p-4">
        <div class="flex justify-between items-start mb-2">
            <h3 class="font-semibold text-gray-900">{{ $product->title }}</h3>
            @if($product->is_free)
            <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">
                FREE
            </span>
            @else
            <span class="text-lg font-bold text-blue-600">₹{{ $product->price }}</span>
            @endif
        </div>

        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($product->description, 80) }}</p>

        <div class="flex justify-between items-center text-sm text-gray-500 mb-3">
            <span>{{ $product->quantity }} {{ $product->unit }}</span>
            <span class="capitalize {{ $product->condition == 'fresh' ? 'text-green-600' : 'text-orange-600' }}">
                {{ $product->condition }}
            </span>
        </div>

        <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
            <div class="flex items-center">
                <span class="text-gray-400 mr-1">📍</span>
                <span>By: {{ $product->user->name }}</span>
            </div>
            @if(isset($product->distance))
            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                {{ $product->distance }}km
            </span>
            @endif
        </div>

        <a href="{{ route('products.show', $product) }}"
            class="w-full bg-blue-500 text-white text-center py-2 rounded-md hover:bg-blue-600 block text-sm">
            View Details
        </a>
    </div>
</div>