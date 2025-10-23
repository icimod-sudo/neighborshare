<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Products') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif

            @if($products->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">My Listed Products ({{ $products->total() }})</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $products->where('is_available', true)->count() }} active •
                                {{ $products->where('is_available', false)->count() }} inactive
                            </p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('products.index') }}"
                                class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                                Browse Products
                            </a>
                            <a href="{{ route('products.create') }}"
                                class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors">
                                + List New Product
                            </a>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                            <div class="text-blue-600 text-2xl font-bold">{{ $products->count() }}</div>
                            <div class="text-blue-800 text-sm">Total Listed</div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                            <div class="text-green-600 text-2xl font-bold">{{ $products->where('is_available', true)->count() }}</div>
                            <div class="text-green-800 text-sm">Active</div>
                        </div>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                            <div class="text-orange-600 text-2xl font-bold">{{ $products->where('is_free', true)->count() }}</div>
                            <div class="text-orange-800 text-sm">Free Items</div>
                        </div>
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                            <div class="text-purple-600 text-2xl font-bold">{{ $products->where('expiry_date', '<=', now())->count() }}</div>
                            <div class="text-purple-800 text-sm">Expired</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow {{ !$product->is_available ? 'bg-gray-50 opacity-75' : '' }}">
                            <!-- Product Status Badge -->
                            <div class="flex justify-between items-start mb-3">
                                @if(!$product->is_available)
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">INACTIVE</span>
                                @else
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">ACTIVE</span>
                                @endif

                                @if($product->expiry_date && $product->expiry_date->isPast())
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">EXPIRED</span>
                                @elseif($product->expiry_date && $product->expiry_date->diffInDays(now()) <= 7)
                                    <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-medium">SOON</span>
                                    @endif
                            </div>

                            <!-- Product Image -->
                            @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                alt="{{ $product->title }}"
                                class="w-full h-40 object-cover rounded-lg mb-3">
                            @else
                            <div class="w-full h-40 bg-gray-200 rounded-lg flex items-center justify-center mb-3">
                                <span class="text-gray-400 text-2xl">📦</span>
                            </div>
                            @endif

                            <!-- Product Details -->
                            <h4 class="font-semibold text-gray-900 mb-1">{{ $product->title }}</h4>
                            <p class="text-gray-600 text-sm mb-2">{{ $product->subcategory }}</p>

                            <!-- Price & Quantity -->
                            <div class="flex justify-between items-center text-sm mb-2">
                                @if($product->is_free)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">FREE</span>
                                @else
                                <span class="font-semibold text-blue-600">₹{{ $product->price }}</span>
                                @endif
                                <span class="text-gray-500">{{ $product->quantity }}{{ $product->unit }}</span>
                            </div>

                            <!-- Condition & Expiry -->
                            <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
                                <span class="capitalize bg-gray-100 px-2 py-1 rounded">
                                    {{ str_replace('_', ' ', $product->condition) }}
                                </span>
                                @if($product->expiry_date)
                                <span class="{{ $product->expiry_date->isPast() ? 'text-red-600' : 'text-orange-600' }}">
                                    {{ $product->expiry_date->format('M d, Y') }}
                                </span>
                                @endif
                            </div>

                            <!-- Listed Date -->
                            <div class="text-xs text-gray-400 mb-3">
                                Listed {{ $product->created_at->diffForHumans() }}
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2">
                                <a href="{{ route('products.show', $product) }}"
                                    class="flex-1 bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 text-sm transition-colors">
                                    👁️ View
                                </a>
                                <a href="{{ route('products.edit', $product) }}"
                                    class="flex-1 bg-yellow-500 text-white text-center py-2 rounded hover:bg-yellow-600 text-sm transition-colors">
                                    ✏️ Edit
                                </a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}"
                                    class="flex-1"
                                    onsubmit="return confirm('Are you sure you want to delete \" {{ $product->title }}\"? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600 text-sm transition-colors">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>

                            <!-- Quick Status Toggle -->
                            <form method="POST" action="{{ route('products.update', $product) }}" class="mt-2">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_available" value="{{ $product->is_available ? '0' : '1' }}">
                                <button type="submit"
                                    onclick="return confirmStatusToggle(this, {{ $product->is_available ? 'true' : 'false' }})"
                                    class="w-full text-xs py-2 rounded border transition-colors font-medium {{ $product->is_available ? 'bg-green-100 text-green-700 border-green-300 hover:bg-green-200' : 'bg-red-100 text-red-700 border-red-300 hover:bg-red-200' }}">
                                    {{ $product->is_available ? '⏸️ Mark as Unavailable' : '▶️ Mark as Available' }}
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($products->hasPages())
                    <div class="mt-6 border-t pt-6">
                        {{ $products->links() }}
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <div class="text-gray-400 text-6xl mb-4">📦</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products listed yet</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        You haven't listed any products yet. Start sharing your items with the community and help reduce food waste!
                    </p>
                    <div class="space-x-4">
                        <a href="{{ route('products.create') }}"
                            class="bg-green-500 text-white px-6 py-3 rounded-md hover:bg-green-600 transition-colors font-medium">
                            🚀 List Your First Product
                        </a>
                        <a href="{{ route('products.index') }}"
                            class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 transition-colors font-medium">
                            🔍 Browse Products
                        </a>
                    </div>
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-2xl mx-auto">
                        <div class="text-center">
                            <div class="text-2xl mb-2">📸</div>
                            <h4 class="font-medium text-gray-900 mb-1">Add Photos</h4>
                            <p class="text-sm text-gray-500">Upload clear images of your items</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl mb-2">📝</div>
                            <h4 class="font-medium text-gray-900 mb-1">Set Details</h4>
                            <p class="text-sm text-gray-500">Describe condition, quantity & price</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl mb-2">🤝</div>
                            <h4 class="font-medium text-gray-900 mb-1">Connect</h4>
                            <p class="text-sm text-gray-500">Share with your local community</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        // Fixed confirmation function
        function confirmStatusToggle(button, currentStatus) {
            const action = currentStatus ? 'deactivate' : 'activate';
            const productTitle = button.closest('.border-gray-200').querySelector('h4').textContent.trim();

            return confirm(`Are you sure you want to ${action} "${productTitle}"?`);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Alternative method using form submission
            const statusForms = document.querySelectorAll('form[action*="update"]');
            statusForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const button = this.querySelector('button[type="submit"]');
                    const currentStatus = button.textContent.includes('Unavailable');
                    const action = currentStatus ? 'deactivate' : 'activate';
                    const productTitle = this.closest('.border-gray-200').querySelector('h4').textContent.trim();

                    if (!confirm(`Are you sure you want to ${action} "${productTitle}"?`)) {
                        e.preventDefault();
                    }
                });
            });

            // Add hover effects
            const productCards = document.querySelectorAll('.border-gray-200');
            productCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>

    <style>
        .transition-colors {
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
        }

        .transition-shadow {
            transition: box-shadow 0.2s ease-in-out, transform 0.2s ease-in-out;
        }

        .border-gray-200 {
            transition: all 0.2s ease-in-out;
        }
    </style>
</x-app-layout>