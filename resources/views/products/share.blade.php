<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Share Product - {{ $product->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-center">
                        <!-- QR Code Display -->
                        <div class="mb-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Scan to View Product</h3>
                            <div class="inline-block bg-white p-4 rounded-lg shadow-lg border border-gray-200">
                                <img src="{{ $qrCodeUrl }}" alt="QR Code for {{ $product->title }}"
                                    class="mx-auto w-64 h-64">
                                <p class="text-sm text-gray-600 mt-2">Scan this QR code to view product details</p>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="mb-8 bg-gray-50 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $product->title }}</h4>
                            <div class="flex justify-center items-center space-x-4 text-sm text-gray-600">
                                @if($product->is_free)
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full font-semibold">
                                    FREE
                                </span>
                                @else
                                <span class="font-semibold text-blue-600">रू {{ $product->price }}</span>
                                @endif
                                <span>{{ $product->quantity }} {{ $product->unit }}</span>
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                                    {{ ucfirst($product->category) }}
                                </span>
                            </div>
                        </div>

                        <!-- Share via Social Media -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Share via</h4>
                            <div class="flex justify-center space-x-4">
                                <!-- WhatsApp -->
                                <a href="https://wa.me/?text={{ urlencode($shareMessage) }}"
                                    target="_blank"
                                    class="p-3 rounded-full transition-colors">
                                    <i class="fab fa-whatsapp text-xl"></i>
                                </a>

                                <!-- Telegram -->
                                <a href="https://t.me/share/url?url={{ urlencode($productUrl) }}&text={{ urlencode($product->title) }}"
                                    target="_blank"
                                    class="p-3 rounded-full transition-colors">
                                    <i class="fab fa-telegram text-xl"></i>
                                </a>

                                <!-- Viber -->
                                <a href="viber://forward?text={{ urlencode($shareMessage) }}"
                                    target="_blank"
                                    class="p-3 rounded-full transition-colors">
                                    <i class="fab fa-viber text-xl"></i>
                                </a>

                                <!-- Facebook -->
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($productUrl) }}&quote={{ urlencode($product->title) }}"
                                    target="_blank"
                                    class="p-3 rounded-full transition-colors">
                                    <i class="fab fa-facebook text-xl"></i>
                                </a>
                            </div>
                        </div>


                        <!-- Product Link -->
                        <div class="bg-gray-100 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Product Link</h4>
                            <div class="flex items-center space-x-2">
                                <input type="text"
                                    value="{{ $productUrl }}"
                                    id="productLink"
                                    readonly
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                                <button onclick="copyLink()"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                    Copy
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Back to Product -->
                    <div class="text-center mt-8">
                        <a href="{{ route('products.show', $product) }}"
                            class="inline-flex items-center space-x-2 text-blue-600 hover:text-blue-800 font-semibold">
                            <span>←</span>
                            <span>Back to Product</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyLink() {
            const linkInput = document.getElementById('productLink');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999);
            document.execCommand('copy');

            // Show confirmation
            alert('Product link copied to clipboard!');
        }

        function shareProduct() {
            const shareMessage = `{{ $shareMessage }}`;

            if (navigator.share) {
                // Use Web Share API if available
                navigator.share({
                        title: '{{ $product->title }} - Gwache App',
                        text: shareMessage,
                        url: '{{ $productUrl }}'
                    })
                    .then(() => console.log('Successful share'))
                    .catch((error) => console.log('Error sharing:', error));
            } else {
                // Fallback: copy to clipboard
                copyLink();
            }
        }

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            // Add any additional initialization here
        });
    </script>

    <style>
        .transition-colors {
            transition: background-color 0.2s ease-in-out;
        }
    </style>
</x-app-layout>