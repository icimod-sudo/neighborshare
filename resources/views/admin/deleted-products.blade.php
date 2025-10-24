@extends('layouts.admin')

@section('title', 'Deleted Products')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Deleted Products</h1>
            <p class="text-gray-600">Manage permanently deleted products and restore if needed</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.products') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Active Products
            </a>
            <a href="{{ route('admin.dashboard') }}"
                class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
@if (session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    {{ session('error') }}
</div>
@endif

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form action="{{ route('admin.deleted-products') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Product title or deletion reason..."
                class="w-full border-gray-300 rounded-md shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select name="category" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">All Categories</option>
                <option value="vegetable" {{ request('category') == 'vegetable' ? 'selected' : '' }}>Vegetables</option>
                <option value="fruit" {{ request('category') == 'fruit' ? 'selected' : '' }}>Fruits</option>
                <option value="plants" {{ request('category') == 'plants' ? 'selected' : '' }}>Plants</option>
                <option value="dairy" {{ request('category') == 'dairy' ? 'selected' : '' }}>Dairy</option>
                <option value="fmcg" {{ request('category') == 'fmcg' ? 'selected' : '' }}>FMCG</option>
                <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        <div class="flex items-center space-x-2">
            <input type="checkbox" name="fraud_only" id="fraud_only" value="1"
                {{ request('fraud_only') ? 'checked' : '' }}
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="fraud_only" class="text-sm font-medium text-gray-700">Fraud Related Only</label>
        </div>

        <div class="flex items-end space-x-2">
            <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                Filter
            </button>
            <a href="{{ route('admin.deleted-products') }}" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors text-center">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Deleted Products Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                Deleted Products ({{ $products->total() }})
            </h3>
            <div class="flex items-center space-x-4">
                @if(request('fraud_only'))
                <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">
                    Fraud Related
                </span>
                @endif
                <span class="text-sm text-gray-500">
                    Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
                </span>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deletion Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($product->images && count($product->images) > 0)
                            <div class="flex-shrink-0 w-10 h-10 bg-gray-200 rounded-lg overflow-hidden">
                                <img src="{{ Storage::url($product->images[0]) }}"
                                    alt="{{ $product->title }}"
                                    class="w-full h-full object-cover">
                            </div>
                            @else
                            <div class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                <span class="text-gray-400 text-lg">📦</span>
                            </div>
                            @endif
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $product->title }}
                                </div>
                                <div class="text-sm text-gray-500 line-clamp-2 max-w-xs">
                                    {{ Str::limit($product->description, 80) }}
                                </div>
                                <div class="flex items-center mt-1">
                                    @if($product->is_free)
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                        Free
                                    </span>
                                    @else
                                    <span class="text-xs text-gray-500">
                                        Exchange
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 capitalize">
                            {{ $product->category }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-gray-600 text-sm font-medium">
                                    {{ substr($product->user->name, 0, 1) }}
                                </span>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $product->user->name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $product->user->email }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">
                            @if($product->deleted_reason)
                            {{ $product->deleted_reason }}
                            @else
                            <span class="text-gray-400">No reason provided</span>
                            @endif
                        </div>
                        @if(str_contains(strtolower($product->deleted_reason ?? ''), 'fraud'))
                        <span class="inline-block mt-1 px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                            Fraud
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div>{{ $product->deleted_at->format('M j, Y') }}</div>
                        <div class="text-gray-400">{{ $product->deleted_at->format('g:i A') }}</div>
                        <div class="text-xs text-gray-400">{{ $product->deleted_at->diffForHumans() }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center space-x-2">
                            <!-- Restore Product Form -->
                            <form action="{{ route('admin.products.restore', $product) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to restore this product?')"
                                    class="text-green-600 hover:text-green-900 text-sm font-medium">
                                    Restore
                                </button>
                            </form>

                            <!-- Permanent Delete -->
                            <form action="{{ route('admin.products.force-delete', $product) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('⚠️ WARNING: This will permanently delete this product and all associated data. This action cannot be undone!')"
                                    class="text-red-600 hover:text-red-900">
                                    Delete Permanently
                                </button>
                            </form>

                            <!-- View Details -->
                            <button type="button"
                                onclick="showProductDetails({{ $product->id }})"
                                class="text-blue-600 hover:text-blue-900">
                                Details
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center">
                        <div class="text-gray-400">
                            <span class="text-4xl">🗑️</span>
                            <p class="mt-2 text-lg font-medium text-gray-900">No deleted products found</p>
                            <p class="text-gray-500">No products match your current filters.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $products->links() }}
    </div>
    @endif
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-gray-100 rounded-lg">
                <span class="text-gray-600 text-2xl">🗑️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Deleted</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_deleted'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
                <span class="text-red-600 text-2xl">🚨</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Fraud Related</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['fraud_related'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-orange-100 rounded-lg">
                <span class="text-orange-600 text-2xl">📅</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Deleted Today</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['deleted_today'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
                <span class="text-green-600 text-2xl">↩️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Restored This Week</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['restored_week'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Product Details Modal -->
<div id="productDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold text-gray-900">Product Details</h3>
                <button onclick="closeProductDetails()" class="text-gray-400 hover:text-gray-600">
                    <span class="text-2xl">×</span>
                </button>
            </div>

            <div class="mt-4" id="productDetailsContent">
                <!-- Content will be loaded via AJAX -->
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button onclick="closeProductDetails()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showProductDetails(productId) {
        // Show loading
        document.getElementById('productDetailsContent').innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
    `;

        // Show modal
        document.getElementById('productDetailsModal').classList.remove('hidden');

        // Load product details via AJAX
        fetch(`/admin/products/${productId}/details`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('productDetailsContent').innerHTML = data.html;
            })
            .catch(error => {
                document.getElementById('productDetailsContent').innerHTML = `
                <div class="text-center text-red-600 py-4">
                    Failed to load product details
                </div>
            `;
            });
    }

    function closeProductDetails() {
        document.getElementById('productDetailsModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('productDetailsModal').addEventListener('click', function(e) {
        if (e.target.id === 'productDetailsModal') {
            closeProductDetails();
        }
    });
</script>
@endpush
@endsection