<!-- Mobile Bottom Navigation - App-like -->
<div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-2 px-4 z-50 shadow-lg">
    <div class="grid grid-cols-4 gap-1">
        <a href="{{ route('dashboard') }}"
            class="flex flex-col items-center p-2 rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600' }}">
            <span class="text-2xl">🏠</span>
            <span class="text-xs mt-1 font-medium">Home</span>
        </a>
        <a href="{{ route('products.index') }}"
            class="flex flex-col items-center p-2 rounded-xl transition-colors {{ request()->routeIs('products.index') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600' }}">
            <span class="text-2xl">🔍</span>
            <span class="text-xs mt-1 font-medium">Browse</span>
        </a>
        <a href="{{ route('products.my') }}"
            class="flex flex-col items-center p-2 rounded-xl transition-colors {{ request()->routeIs('products.my') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600' }}">
            <span class="text-2xl">📦</span>
            <span class="text-xs mt-1 font-medium">My Items</span>
        </a>
        <a href="{{ route('exchanges.my') }}"
            class="flex flex-col items-center p-2 rounded-xl transition-colors {{ request()->routeIs('exchanges.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600' }}">
            <span class="text-2xl">🔄</span>
            <span class="text-xs mt-1 font-medium">Trades</span>
        </a>
    </div>
</div>