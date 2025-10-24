<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Products') }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
            <!-- Success Message -->
            @if(session('success'))
            <div class="mb-4 sm:mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-sm sm:text-base">
                {{ session('success') }}
            </div>
            @endif

            @if($products->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <!-- Header Section -->
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4 sm:mb-6">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">My Listed Products ({{ $products->total() }})</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $products->where('is_available', true)->count() }} active •
                                {{ $products->where('is_available', false)->count() }} inactive
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 sm:space-x-3">
                            <a href="{{ route('products.index') }}"
                                class="bg-gray-500 text-white px-3 sm:px-4 py-2 rounded-md hover:bg-gray-600 transition-colors text-sm sm:text-base text-center min-h-[44px] flex items-center justify-center">
                                <span class="hidden sm:inline">Browse Products</span>
                                <span class="sm:hidden">Browse</span>
                            </a>
                            <a href="{{ route('products.create') }}"
                                class="bg-green-500 text-white px-3 sm:px-4 py-2 rounded-md hover:bg-green-600 transition-colors text-sm sm:text-base text-center min-h-[44px] flex items-center justify-center">
                                + List New Product
                            </a>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mb-4 sm:mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 text-center">
                            <div class="text-blue-600 text-lg sm:text-2xl font-bold">{{ $products->count() }}</div>
                            <div class="text-blue-800 text-xs sm:text-sm">Total Listed</div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 text-center">
                            <div class="text-green-600 text-lg sm:text-2xl font-bold">{{ $products->where('is_available', true)->count() }}</div>
                            <div class="text-green-800 text-xs sm:text-sm">Active</div>
                        </div>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 sm:p-4 text-center">
                            <div class="text-orange-600 text-lg sm:text-2xl font-bold">{{ $products->where('is_free', true)->count() }}</div>
                            <div class="text-orange-800 text-xs sm:text-sm">Free Items</div>
                        </div>
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 sm:p-4 text-center">
                            <div class="text-purple-600 text-lg sm:text-2xl font-bold">{{ $products->where('expiry_date', '<=', now())->count() }}</div>
                            <div class="text-purple-800 text-xs sm:text-sm">Expired</div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($products as $product)
                        <div class="border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-md transition-shadow {{ !$product->is_available ? 'bg-gray-50 opacity-75' : '' }}">
                            <!-- Product Status Badge -->
                            <div class="flex justify-between items-start mb-2 sm:mb-3 flex-wrap gap-1">
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
                                class="w-full h-32 sm:h-40 object-cover rounded-lg mb-2 sm:mb-3">
                            @else
                            <div class="w-full h-32 sm:h-40 bg-gray-200 rounded-lg flex items-center justify-center mb-2 sm:mb-3">
                                <span class="text-gray-400 text-xl sm:text-2xl">📦</span>
                            </div>
                            @endif

                            <!-- Product Details -->
                            <h4 class="font-semibold text-gray-900 mb-1 text-sm sm:text-base line-clamp-2">{{ $product->title }}</h4>
                            <p class="text-gray-600 text-xs sm:text-sm mb-2">{{ $product->subcategory }}</p>

                            <!-- Price & Quantity -->
                            <div class="flex justify-between items-center text-xs sm:text-sm mb-2 flex-wrap gap-1">
                                @if($product->is_free)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">FREE</span>
                                @else
                                <span class="font-semibold text-blue-600">₹{{ $product->price }}</span>
                                @endif
                                <span class="text-gray-500">{{ $product->quantity }}{{ $product->unit }}</span>
                            </div>

                            <!-- Condition & Expiry -->
                            <div class="flex justify-between items-center text-xs text-gray-500 mb-2 sm:mb-3 flex-wrap gap-1">
                                <span class="capitalize bg-gray-100 px-2 py-1 rounded">
                                    {{ str_replace('_', ' ', $product->condition) }}
                                </span>
                                @if($product->expiry_date)
                                <span class="{{ $product->expiry_date->isPast() ? 'text-red-600' : 'text-orange-600' }} text-xs">
                                    {{ $product->expiry_date->format('M d, Y') }}
                                </span>
                                @endif
                            </div>

                            <!-- Listed Date -->
                            <div class="text-xs text-gray-400 mb-2 sm:mb-3">
                                Listed {{ $product->created_at->diffForHumans() }}
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2 mb-2">
                                <a href="{{ route('products.show', $product) }}"
                                    class="flex-1 bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 text-xs sm:text-sm transition-colors min-h-[44px] flex items-center justify-center">
                                    <span class="sm:mr-1">👁️</span>
                                    <span class="hidden sm:inline">View</span>
                                </a>
                                <a href="{{ route('products.edit', $product) }}"
                                    class="flex-1 bg-yellow-500 text-white text-center py-2 rounded hover:bg-yellow-600 text-xs sm:text-sm transition-colors min-h-[44px] flex items-center justify-center">
                                    <span class="sm:mr-1">✏️</span>
                                    <span class="hidden sm:inline">Edit</span>
                                </a>
                                <!-- <form method="POST" action="{{ route('products.destroy', $product) }}"
                                    class="flex-1"
                                    onsubmit="return confirm('Are you sure you want to delete \" {{ $product->title }}\"? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600 text-xs sm:text-sm transition-colors min-h-[44px] flex items-center justify-center">
                                        <span class="sm:mr-1">🗑️</span>
                                        <span class="hidden sm:inline">Delete</span>
                                    </button>
                                </form> -->
                            </div>

                            <!-- Quick Status Toggle -->
                            <!-- <form method="POST" action="{{ route('products.update', $product) }}" class="mt-2">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_available" value="{{ $product->is_available ? '0' : '1' }}">
                                <button type="submit"
                                    onclick="return confirmStatusToggle(this, {{ $product->is_available ? 'true' : 'false' }})"
                                    class="w-full text-xs py-2 rounded border transition-colors font-medium min-h-[44px] flex items-center justify-center {{ $product->is_available ? 'bg-green-100 text-green-700 border-green-300 hover:bg-green-200' : 'bg-red-100 text-red-700 border-red-300 hover:bg-red-200' }}">
                                    {{ $product->is_available ? '⏸️ Unavailable' : '▶️ Available' }}
                                </button>
                            </form> -->
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($products->hasPages())
                    <div class="mt-4 sm:mt-6 border-t pt-4 sm:pt-6">
                        {{ $products->links() }}
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-12 text-center">
                    <div class="text-gray-400 text-4xl sm:text-6xl mb-4">📦</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products listed yet</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto text-sm sm:text-base">
                        You haven't listed any products yet. Start sharing your items with the community and help reduce food waste!
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('products.create') }}"
                            class="bg-green-500 text-white px-4 sm:px-6 py-3 rounded-md hover:bg-green-600 transition-colors font-medium text-sm sm:text-base min-h-[44px] flex items-center justify-center">
                            🚀 List Your First Product
                        </a>
                        <a href="{{ route('products.index') }}"
                            class="bg-blue-500 text-white px-4 sm:px-6 py-3 rounded-md hover:bg-blue-600 transition-colors font-medium text-sm sm:text-base min-h-[44px] flex items-center justify-center">
                            🔍 Browse Products
                        </a>
                    </div>
                    <div class="mt-6 sm:mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 max-w-2xl mx-auto">
                        <div class="text-center">
                            <div class="text-xl sm:text-2xl mb-2">📸</div>
                            <h4 class="font-medium text-gray-900 mb-1 text-sm sm:text-base">Add Photos</h4>
                            <p class="text-xs sm:text-sm text-gray-500">Upload clear images of your items</p>
                        </div>
                        <div class="text-center">
                            <div class="text-xl sm:text-2xl mb-2">📝</div>
                            <h4 class="font-medium text-gray-900 mb-1 text-sm sm:text-base">Set Details</h4>
                            <p class="text-xs sm:text-sm text-gray-500">Describe condition, quantity & price</p>
                        </div>
                        <div class="text-center">
                            <div class="text-xl sm:text-2xl mb-2">🤝</div>
                            <h4 class="font-medium text-gray-900 mb-1 text-sm sm:text-base">Connect</h4>
                            <p class="text-xs sm:text-sm text-gray-500">Share with your local community</p>
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

            // Add hover effects for desktop only
            if (window.innerWidth > 768) {
                const productCards = document.querySelectorAll('.border-gray-200');
                productCards.forEach(card => {
                    card.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-2px)';
                    });
                    card.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0)';
                    });
                });
            }
        });

        // Add line-clamp utility if not present
        const style = document.createElement('style');
        style.textContent = `
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .transition-colors {
                transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
            }
            .transition-shadow {
                transition: box-shadow 0.2s ease-in-out, transform 0.2s ease-in-out;
            }
            .border-gray-200 {
                transition: all 0.2s ease-in-out;
            }
            @media (max-width: 640px) {
                .border-gray-200 {
                    transform: none !important;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</x-app-layout>