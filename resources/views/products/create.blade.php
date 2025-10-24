<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('List New Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Product Status Info -->
                        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-green-500 text-lg">🟢</span>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        New products are active by default
                                    </h3>
                                    <p class="text-sm text-green-700 mt-1">
                                        Your product will be visible to others immediately after creation.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden field to ensure product is available -->
                        <input type="hidden" name="is_available" value="1">

                        <!-- Basic Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                            <!-- Title -->
                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700">Product Title *</label>
                                <input type="text" name="title" id="title" required
                                    value="{{ old('title') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                                <textarea name="description" id="description" rows="4" required
                                    placeholder="Describe your product in detail..."
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                                @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Category & Type -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Category & Type</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <!-- Category -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                                    <select name="category" id="category" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Category</option>
                                        <option value="vegetable" {{ old('category') == 'vegetable' ? 'selected' : '' }}>Vegetables</option>
                                        <option value="fruit" {{ old('category') == 'fruit' ? 'selected' : '' }}>Fruits</option>
                                        <option value="plants" {{ old('category') == 'plants' ? 'selected' : '' }}>Plants</option>
                                        <option value="fmcg" {{ old('category') == 'fmcg' ? 'selected' : '' }}>FMCG Products</option>
                                        <option value="dairy" {{ old('category') == 'dairy' ? 'selected' : '' }}>Dairy</option>
                                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('category')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Subcategory -->
                                <div>
                                    <label for="subcategory" class="block text-sm font-medium text-gray-700">Specific Item *</label>
                                    <input type="text" name="subcategory" id="subcategory" required
                                        value="{{ old('subcategory') }}"
                                        placeholder="e.g., Tomatoes, Rice, Milk, Bread"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('subcategory')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Quantity & Condition -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quantity & Condition</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                <!-- Quantity -->
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                                    <input type="number" name="quantity" id="quantity" step="0.1" min="0.1" required
                                        value="{{ old('quantity') }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('quantity')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Unit -->
                                <div>
                                    <label for="unit" class="block text-sm font-medium text-gray-700">Unit *</label>
                                    <select name="unit" id="unit" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Unit</option>
                                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                        <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>Gram (g)</option>
                                        <option value="pieces" {{ old('unit') == 'pieces' ? 'selected' : '' }}>Pieces</option>
                                        <option value="packets" {{ old('unit') == 'packets' ? 'selected' : '' }}>Packets</option>
                                        <option value="liters" {{ old('unit') == 'liters' ? 'selected' : '' }}>Liters</option>
                                        <option value="bunch" {{ old('unit') == 'bunch' ? 'selected' : '' }}>Bunch</option>
                                    </select>
                                    @error('unit')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Condition -->
                                <div>
                                    <label for="condition" class="block text-sm font-medium text-gray-700">Condition *</label>
                                    <select name="condition" id="condition" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Condition</option>
                                        <option value="fresh" {{ old('condition') == 'fresh' ? 'selected' : '' }}>Fresh</option>
                                        <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Good</option>
                                        <option value="average" {{ old('condition') == 'average' ? 'selected' : '' }}>Average</option>
                                        <option value="expiring_soon" {{ old('condition') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                                    </select>
                                    @error('condition')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing</h3>

                            <!-- Free/Paid Toggle -->
                            <div class="mb-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_free" id="is_free" value="1"
                                        {{ old('is_free') ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <label for="is_free" class="ms-2 text-sm font-medium text-gray-700">
                                        This is a free item (no cost)
                                    </label>
                                </div>
                            </div>

                            <!-- Price Input (hidden if free) -->
                            <div id="priceSection" class="{{ old('is_free') ? 'hidden' : '' }}">
                                <label for="price" class="block text-sm font-medium text-gray-700">Price (रू)</label>
                                <input type="number" name="price" id="price" step="0.01" min="0"
                                    value="{{ old('price') }}"
                                    placeholder="0.00"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('price')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <!-- Expiry Date -->
                                <div>
                                    <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date (Optional)</label>
                                    <input type="date" name="expiry_date" id="expiry_date"
                                        value="{{ old('expiry_date') }}"
                                        min="{{ date('Y-m-d') }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('expiry_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">Leave empty if no expiry date</p>
                                </div>

                                <!-- Image Upload -->
                                <div>
                                    <label for="image" class="block text-sm font-medium text-gray-700">Product Image (Optional)</label>
                                    <input type="file" name="image" id="image" accept="image/*"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('image')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror

                                    <!-- Image Preview Container -->
                                    <div id="imagePreviewContainer" class="mt-3 hidden">
                                        <p class="text-sm text-gray-600 mb-2">Image Preview:</p>
                                        <div class="relative inline-block">
                                            <img id="imagePreview" src="#" alt="Preview"
                                                class="h-32 w-32 object-cover rounded-lg border border-gray-300 shadow-sm">
                                            <button type="button" id="removeImageBtn"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors">
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('products.index') }}"
                                class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition-colors font-medium">
                                🚀 List Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle price field based on free checkbox
        document.getElementById('is_free').addEventListener('change', function() {
            const priceSection = document.getElementById('priceSection');
            const priceInput = document.getElementById('price');

            if (this.checked) {
                priceSection.classList.add('hidden');
                priceInput.removeAttribute('required');
                priceInput.value = '';
            } else {
                priceSection.classList.remove('hidden');
                priceInput.setAttribute('required', 'required');
            }
        });

        // Set minimum date for expiry date to today
        document.getElementById('expiry_date').min = new Date().toISOString().split('T')[0];

        // Image Preview Functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('imagePreviewContainer');
            const preview = document.getElementById('imagePreview');
            const removeBtn = document.getElementById('removeImageBtn');

            if (file) {
                // Check if file is an image
                if (!file.type.match('image.*')) {
                    alert('Please select a valid image file (JPEG, PNG, JPG, GIF).');
                    this.value = '';
                    return;
                }

                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Image size should be less than 2MB.');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                }

                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('hidden');
                preview.src = '#';
            }
        });

        // Remove image functionality
        document.getElementById('removeImageBtn').addEventListener('click', function() {
            const fileInput = document.getElementById('image');
            const previewContainer = document.getElementById('imagePreviewContainer');
            const preview = document.getElementById('imagePreview');

            fileInput.value = '';
            preview.src = '#';
            previewContainer.classList.add('hidden');
        });

        // Initialize on page load to handle form validation errors
        document.addEventListener('DOMContentLoaded', function() {
            // Check if free checkbox should be checked based on old input
            const isFreeCheckbox = document.getElementById('is_free');
            if (isFreeCheckbox.checked) {
                isFreeCheckbox.dispatchEvent(new Event('change'));
            }

            // Check if there's an image from form validation errors
            const fileInput = document.getElementById('image');
            if (fileInput.files.length > 0) {
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    </script>

    <style>
        .transition-colors {
            transition: background-color 0.2s ease-in-out;
        }

        #imagePreviewContainer {
            transition: all 0.3s ease;
        }

        #removeImageBtn {
            transition: background-color 0.2s ease;
        }

        #imagePreview {
            transition: transform 0.2s ease;
        }

        #imagePreview:hover {
            transform: scale(1.05);
        }
    </style>
</x-app-layout>