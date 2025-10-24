<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Basic Information -->
    <div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Basic Information</h4>
        <dl class="space-y-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Title</dt>
                <dd class="text-sm text-gray-900">{{ $product->title }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Category</dt>
                <dd class="text-sm text-gray-900 capitalize">{{ $product->category }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Type</dt>
                <dd class="text-sm text-gray-900">
                    @if($product->is_free)
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Free</span>
                    @else
                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Exchange</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="text-sm text-gray-900 mt-1">{{ $product->description }}</dd>
            </div>
        </dl>
    </div>

    <!-- Deletion Information -->
    <div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Deletion Information</h4>
        <dl class="space-y-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Deleted At</dt>
                <dd class="text-sm text-gray-900">{{ $product->deleted_at->format('M j, Y g:i A') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Deletion Reason</dt>
                <dd class="text-sm text-gray-900">
                    @if($product->deleted_reason)
                    {{ $product->deleted_reason }}
                    @else
                    <span class="text-gray-400">No reason provided</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Time Since Deletion</dt>
                <dd class="text-sm text-gray-900">{{ $product->deleted_at->diffForHumans() }}</dd>
            </div>
        </dl>
    </div>

    <!-- Owner Information -->
    <div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Owner Information</h4>
        <dl class="space-y-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Name</dt>
                <dd class="text-sm text-gray-900">{{ $product->user->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="text-sm text-gray-900">{{ $product->user->email }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="text-sm text-gray-900">
                    @if($product->user->trashed())
                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Banned</span>
                    @elseif($product->user->isSuspended())
                    <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full">Suspended</span>
                    @else
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    <!-- Product Statistics -->
    <div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Product Statistics</h4>
        <dl class="space-y-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="text-sm text-gray-900">{{ $product->created_at->format('M j, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Total Exchanges</dt>
                <dd class="text-sm text-gray-900">{{ $product->exchanges->count() }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Completed Exchanges</dt>
                <dd class="text-sm text-gray-900">
                    {{ $product->exchanges->where('status', 'completed')->count() }}
                </dd>
            </div>
        </dl>
    </div>
</div>

<!-- Images Section -->
@if($product->images && count($product->images) > 0)
<div class="mt-6">
    <h4 class="text-lg font-semibold text-gray-900 mb-3">Product Images</h4>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($product->images as $image)
        <div class="bg-gray-100 rounded-lg overflow-hidden">
            <img src="{{ Storage::url($image) }}"
                alt="Product image {{ $loop->iteration }}"
                class="w-full h-24 object-cover">
        </div>
        @endforeach
    </div>
</div>
@endif