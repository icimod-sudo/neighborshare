<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Community Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Your community participation stats and information.") }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <!-- Community Stats -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-green-900 mb-3">Your Community Impact</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $user->total_exchanges }}</div>
                    <div class="text-green-700">Successful Exchanges</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $user->products->count() }}</div>
                    <div class="text-green-700">Products Listed</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex space-x-4">
            <a href="{{ route('products.create') }}"
                class="flex-1 bg-green-500 text-white text-center py-2 px-4 rounded-md hover:bg-green-600 transition-colors text-sm">
                + List New Product
            </a>
            <a href="{{ route('products.my') }}"
                class="flex-1 bg-blue-500 text-white text-center py-2 px-4 rounded-md hover:bg-blue-600 transition-colors text-sm">
                View My Products
            </a>
        </div>
    </div>
</section>