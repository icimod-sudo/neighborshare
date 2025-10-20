<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $product->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <!-- Product Image -->
                        <div>
                            @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                alt="{{ $product->title }}"
                                class="w-full h-96 object-cover rounded-lg shadow-md">
                            @else
                            <div class="w-full h-96 bg-gray-200 rounded-lg shadow-md flex items-center justify-center">
                                <div class="text-center">
                                    <span class="text-6xl text-gray-400 mb-2">📦</span>
                                    <p class="text-gray-500">No Image Available</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Product Details -->
                        <div class="space-y-6">

                            <!-- Title & Price -->
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->title }}</h1>
                                <div class="flex items-center space-x-4 mb-4">
                                    @if($product->is_free)
                                    <span class="bg-green-100 text-green-800 text-lg font-semibold px-4 py-1 rounded-full">
                                        FREE
                                    </span>
                                    @else
                                    <span class="text-3xl font-bold text-blue-600">₹{{ $product->price }}</span>
                                    @endif
                                    <span class="text-lg text-gray-600">{{ $product->quantity }} {{ $product->unit }}</span>
                                </div>
                            </div>

                            <!-- Condition & Category -->
                            <div class="flex flex-wrap gap-2">
                                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {{ ucfirst($product->category) }}
                                </span>
                                <span class="bg-{{ $product->condition == 'fresh' ? 'green' : 'orange' }}-100 text-{{ $product->condition == 'fresh' ? 'green' : 'orange' }}-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {{ ucfirst(str_replace('_', ' ', $product->condition)) }}
                                </span>
                                <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {{ $product->subcategory }}
                                </span>
                            </div>

                            <!-- Description -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                                <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                            </div>

                            <!-- Additional Info -->
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-500">Listed On:</span>
                                    <p class="text-gray-900">{{ $product->created_at->format('M d, Y') }}</p>
                                </div>
                                @if($product->expiry_date)
                                <div>
                                    <span class="font-medium text-gray-500">Expiry Date:</span>
                                    <p class="text-gray-900">{{ $product->expiry_date->format('M d, Y') }}</p>
                                </div>
                                @endif
                                <div>
                                    <span class="font-medium text-gray-500">Status:</span>
                                    <p class="text-gray-900">
                                        <span class="{{ $product->is_available ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                            {{ $product->is_available ? 'Available' : 'Not Available' }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- Seller Information -->
                            <div class="border-t pt-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Shared By</h3>
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold">
                                            {{ substr($product->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $product->user->name }}</p>
                                        <p class="text-sm text-gray-500">Member since {{ $product->user->created_at->format('M Y') }}</p>
                                        <p class="text-sm text-gray-500">{{ $product->user->total_exchanges }} successful exchanges</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="border-t pt-6">
                                @if(Auth::id() === $product->user_id)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <p class="text-blue-800 text-sm">This is your listed product.</p>
                                    <div class="mt-2 flex space-x-3">
                                        <a href="{{ route('products.my') }}"
                                            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 text-sm">
                                            View My Products
                                        </a>
                                    </div>
                                </div>
                                @elseif($product->is_available)
                                <!-- Exchange Request Form -->
                                <form method="POST" action="{{ route('exchanges.store', $product) }}" class="space-y-4">
                                    @csrf

                                    <h3 class="text-lg font-semibold text-gray-900">Request this Item</h3>

                                    <!-- Message -->
                                    <div>
                                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                                            Message to {{ $product->user->name }} *
                                        </label>
                                        <textarea name="message" id="message" rows="3" required
                                            placeholder="Introduce yourself and suggest how to exchange..."
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    </div>

                                    <!-- Exchange Type -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Exchange Type *</label>
                                        <div class="space-y-2">
                                            <div class="flex items-center">
                                                <input type="radio" name="type" id="type_free" value="free"
                                                    {{ $product->is_free ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                                <label for="type_free" class="ml-2 text-sm text-gray-700">
                                                    Free Pickup
                                                </label>
                                            </div>
                                            @if(!$product->is_free)
                                            <div class="flex items-center">
                                                <input type="radio" name="type" id="type_paid" value="paid" checked class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                                <label for="type_paid" class="ml-2 text-sm text-gray-700">
                                                    Pay ₹{{ $product->price }}
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="radio" name="type" id="type_barter" value="barter" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                                <label for="type_barter" class="ml-2 text-sm text-gray-700">
                                                    Barter/Trade
                                                </label>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Offer Price (for barter) -->
                                    <div id="offerPriceSection" class="hidden">
                                        <label for="offer_price" class="block text-sm font-medium text-gray-700 mb-1">
                                            Your Offer (₹)
                                        </label>
                                        <input type="number" name="offer_price" id="offer_price" step="0.01" min="0"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <button type="submit"
                                        class="w-full bg-green-500 text-white py-3 rounded-md hover:bg-green-600 font-semibold transition-colors">
                                        Send Exchange Request
                                    </button>
                                </form>
                                @else
                                <div class="bg-gray-100 border border-gray-300 rounded-lg p-4 text-center">
                                    <p class="text-gray-600">This item is no longer available for exchange.</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- In the product show view, update the exchange form script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const offerSection = document.getElementById('offerPriceSection');

            typeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'barter') {
                        offerSection.classList.remove('hidden');
                        document.getElementById('offer_price').setAttribute('required', 'required');
                    } else {
                        offerSection.classList.add('hidden');
                        document.getElementById('offer_price').removeAttribute('required');
                    }
                });
            });

            // Initialize on page load
            const selectedType = document.querySelector('input[name="type"]:checked');
            if (selectedType && selectedType.value === 'barter') {
                offerSection.classList.remove('hidden');
            }
        });
    </script>
</x-app-layout>