<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $product->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif

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
                                @php
                                $conditionColors = [
                                'fresh' => 'green',
                                'good' => 'blue',
                                'average' => 'yellow',
                                'expiring_soon' => 'red'
                                ];
                                $color = $conditionColors[$product->condition] ?? 'gray';
                                @endphp
                                <span class="bg-{{ $color }}-100 text-{{ $color }}-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {{ ucfirst(str_replace('_', ' ', $product->condition)) }}
                                </span>
                                <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                                    {{ $product->subcategory }}
                                </span>
                            </div>

                            <!-- Description -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
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
                                    <p class="text-gray-900 {{ $product->expiry_date->isPast() ? 'text-red-600 font-semibold' : '' }}">
                                        {{ $product->expiry_date->format('M d, Y') }}
                                        @if($product->expiry_date->isPast())
                                        (Expired)
                                        @endif
                                    </p>
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
                                        @if($product->user->neighborhood)
                                        <p class="text-sm text-gray-500">{{ $product->user->neighborhood }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="border-t pt-6">
                                @if(Auth::id() === $product->user_id)
                                <div class="space-y-4">
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-blue-800 text-sm font-medium">This is your listed product.</p>

                                        <!-- Status Toggle -->
                                        <form method="POST" action="{{ route('products.update', $product) }}" class="mt-3">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="is_available" value="{{ $product->is_available ? '0' : '1' }}">
                                            <button type="submit"
                                                onclick="return confirm('Are you sure you want to {{ $product->is_available ? 'mark as unavailable' : 'mark as available' }} this product?')"
                                                class="w-full {{ $product->is_available ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }} text-white py-2 px-4 rounded-md transition-colors font-medium">
                                                {{ $product->is_available ? '⏸️ Mark as Unavailable' : '▶️ Mark as Available' }}
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Edit and Delete Buttons -->
                                    <div class="flex space-x-3">
                                        <a href="{{ route('products.edit', $product) }}"
                                            class="flex-1 bg-blue-500 text-white text-center py-2 px-4 rounded-md hover:bg-blue-600 transition-colors">
                                            ✏️ Edit Product
                                        </a>
                                        <form method="POST" action="{{ route('products.destroy', $product) }}"
                                            class="flex-1"
                                            onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-full bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-600 transition-colors">
                                                🗑️ Delete Product
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Additional Actions -->
                                    <div class="flex space-x-2">
                                        <a href="{{ route('products.my') }}"
                                            class="flex-1 bg-gray-500 text-white text-center py-2 rounded-md hover:bg-gray-600 text-sm transition-colors">
                                            View My Products
                                        </a>
                                        <a href="{{ route('products.index') }}"
                                            class="flex-1 bg-green-500 text-white text-center py-2 rounded-md hover:bg-green-600 text-sm transition-colors">
                                            Browse More Products
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
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('message') }}</textarea>
                                        @error('message')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
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
                                                <input type="radio" name="type" id="type_paid" value="paid"
                                                    {{ old('type', 'paid') == 'paid' ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                                <label for="type_paid" class="ml-2 text-sm text-gray-700">
                                                    Pay ₹{{ $product->price }}
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="radio" name="type" id="type_barter" value="barter"
                                                    {{ old('type') == 'barter' ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                                <label for="type_barter" class="ml-2 text-sm text-gray-700">
                                                    Barter/Trade
                                                </label>
                                            </div>
                                            @endif
                                        </div>
                                        @error('type')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Offer Price (for barter) -->
                                    <div id="offerPriceSection" class="hidden">
                                        <label for="offer_price" class="block text-sm font-medium text-gray-700 mb-1">
                                            Your Offer (₹)
                                        </label>
                                        <input type="number" name="offer_price" id="offer_price" step="0.01" min="0"
                                            value="{{ old('offer_price') }}"
                                            placeholder="Enter your offer amount"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('offer_price')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Contact Information -->
                                    <div>
                                        <label for="contact_info" class="block text-sm font-medium text-gray-700 mb-1">
                                            Your Contact Information *
                                        </label>
                                        <input type="text" name="contact_info" id="contact_info" required
                                            value="{{ old('contact_info', Auth::user()->email) }}"
                                            placeholder="Phone number or email for contact"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('contact_info')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <button type="submit"
                                        class="w-full bg-green-500 text-white py-3 rounded-md hover:bg-green-600 font-semibold transition-colors duration-200">
                                        📨 Send Exchange Request
                                    </button>

                                    <p class="text-xs text-gray-500 text-center">
                                        By sending this request, you agree to coordinate the exchange details with the seller.
                                    </p>
                                </form>
                                @else
                                <div class="bg-gray-100 border border-gray-300 rounded-lg p-6 text-center">
                                    <div class="text-gray-400 text-4xl mb-2">🔒</div>
                                    <p class="text-gray-600 font-medium mb-2">This item is no longer available</p>
                                    <p class="text-gray-500 text-sm">This product has been marked as unavailable by the seller.</p>

                                    <!-- Suggest similar available products -->
                                    @php
                                    $similarProducts = App\Models\Product::where('category', $product->category)
                                    ->where('id', '!=', $product->id)
                                    ->where('is_available', true)
                                    ->take(3)
                                    ->get();
                                    @endphp

                                    @if($similarProducts->count() > 0)
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-600 mb-2">Check out similar available items:</p>
                                        <div class="space-y-2">
                                            @foreach($similarProducts as $similar)
                                            <a href="{{ route('products.show', $similar) }}"
                                                class="block text-blue-600 hover:text-blue-800 text-sm">
                                                • {{ $similar->title }} - {{ $similar->is_free ? 'FREE' : '₹'.$similar->price }}
                                            </a>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <a href="{{ route('products.index') }}"
                                        class="inline-block mt-4 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                                        Browse Available Products
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Related Products Section -->
                    @if(!$product->user_id == Auth::id())
                    <div class="mt-12 border-t pt-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">More from this Seller</h3>
                        @php
                        $sellerProducts = App\Models\Product::where('user_id', $product->user_id)
                        ->where('id', '!=', $product->id)
                        ->where('is_available', true)
                        ->take(4)
                        ->get();
                        @endphp

                        @if($sellerProducts->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach($sellerProducts as $relatedProduct)
                            <a href="{{ route('products.show', $relatedProduct) }}"
                                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                @if($relatedProduct->image)
                                <img src="{{ asset('storage/' . $relatedProduct->image) }}"
                                    alt="{{ $relatedProduct->title }}"
                                    class="w-full h-32 object-cover rounded-lg mb-2">
                                @else
                                <div class="w-full h-32 bg-gray-200 rounded-lg flex items-center justify-center mb-2">
                                    <span class="text-gray-400 text-xl">📦</span>
                                </div>
                                @endif
                                <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ Str::limit($relatedProduct->title, 40) }}</h4>
                                <div class="flex justify-between items-center text-xs">
                                    @if($relatedProduct->is_free)
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded">FREE</span>
                                    @else
                                    <span class="font-semibold text-blue-600">₹{{ $relatedProduct->price }}</span>
                                    @endif
                                    <span class="text-gray-500">{{ $relatedProduct->quantity }}{{ $relatedProduct->unit }}</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <p class="text-gray-500 text-center py-4">No other products from this seller.</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const offerSection = document.getElementById('offerPriceSection');

            function toggleOfferSection() {
                const selectedType = document.querySelector('input[name="type"]:checked');
                if (selectedType && selectedType.value === 'barter') {
                    offerSection.classList.remove('hidden');
                    document.getElementById('offer_price').setAttribute('required', 'required');
                } else {
                    offerSection.classList.add('hidden');
                    document.getElementById('offer_price').removeAttribute('required');
                    document.getElementById('offer_price').value = '';
                }
            }

            typeRadios.forEach(radio => {
                radio.addEventListener('change', toggleOfferSection);
            });

            // Initialize on page load
            toggleOfferSection();

            // Form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const message = document.getElementById('message').value.trim();
                    if (!message) {
                        e.preventDefault();
                        alert('Please enter a message for the seller.');
                        document.getElementById('message').focus();
                    }
                });
            }

            // Status toggle confirmation
            const statusForm = document.querySelector('form[action*="update"] input[name="is_available"]');
            if (statusForm) {
                const form = statusForm.closest('form');
                form.addEventListener('submit', function(e) {
                    const isActive = this.querySelector('input[name="is_available"]').value === '1';
                    const action = isActive ? 'make available' : 'mark as unavailable';

                    if (!confirm(`Are you sure you want to ${action} this product?`)) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>

    <style>
        .transition-colors {
            transition: background-color 0.2s ease-in-out;
        }

        .transition-shadow {
            transition: box-shadow 0.2s ease-in-out;
        }

        .whitespace-pre-line {
            white-space: pre-line;
        }
    </style>
</x-app-layout>