<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Exchange Details') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <!-- Exchange Status Banner -->
                    <div class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-lg 
                        @if($exchange->status == 'pending') bg-yellow-50 border border-yellow-200
                        @elseif($exchange->status == 'accepted') bg-blue-50 border border-blue-200
                        @elseif($exchange->status == 'completed') bg-green-50 border border-green-200
                        @else bg-red-50 border border-red-200
                        @endif">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center">
                                <span class="text-xl sm:text-2xl mr-2 sm:mr-3">
                                    @if($exchange->status == 'pending') ⏳
                                    @elseif($exchange->status == 'accepted') ✅
                                    @elseif($exchange->status == 'completed') 🎉
                                    @else ❌
                                    @endif
                                </span>
                                <div>
                                    <h3 class="text-base sm:text-lg font-semibold 
                                        @if($exchange->status == 'pending') text-yellow-800
                                        @elseif($exchange->status == 'accepted') text-blue-800
                                        @elseif($exchange->status == 'completed') text-green-800
                                        @else text-red-800
                                        @endif">
                                        Exchange {{ ucfirst($exchange->status) }}
                                    </h3>
                                    <p class="text-xs sm:text-sm 
                                        @if($exchange->status == 'pending') text-yellow-600
                                        @elseif($exchange->status == 'accepted') text-blue-600
                                        @elseif($exchange->status == 'completed') text-green-600
                                        @else text-red-600
                                        @endif">
                                        @if($exchange->status == 'pending')
                                        Waiting for seller response
                                        @elseif($exchange->status == 'accepted')
                                        Exchange accepted! Coordinate pickup/delivery
                                        @elseif($exchange->status == 'completed')
                                        Successfully completed on {{ $exchange->updated_at->format('M j, Y') }}
                                        @else
                                        Cancelled on {{ $exchange->updated_at->format('M j, Y') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <span class="px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium self-start sm:self-auto
                                @if($exchange->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($exchange->status == 'accepted') bg-blue-100 text-blue-800
                                @elseif($exchange->status == 'completed') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($exchange->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                        <!-- Product Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Product Details</h3>
                            <div class="border border-gray-200 rounded-lg p-3 sm:p-4">
                                @if($exchange->product->image)
                                <img src="{{ asset('storage/' . $exchange->product->image) }}"
                                    alt="{{ $exchange->product->title }}"
                                    class="w-full h-32 sm:h-48 object-cover rounded-lg mb-3 sm:mb-4">
                                @else
                                <div class="w-full h-32 sm:h-48 bg-gray-200 rounded-lg flex items-center justify-center mb-3 sm:mb-4">
                                    <span class="text-gray-400 text-xl sm:text-2xl">📦</span>
                                </div>
                                @endif

                                <h4 class="font-semibold text-gray-900 text-base sm:text-lg">{{ $exchange->product->title }}</h4>
                                <p class="text-gray-600 text-sm sm:text-base mb-2">{{ $exchange->product->subcategory }}</p>

                                <div class="space-y-2 text-xs sm:text-sm">
                                    <!-- Available Quantity -->
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Available Quantity:</span>
                                        <span class="font-medium">{{ $exchange->product->quantity }} {{ $exchange->product->unit }}</span>
                                    </div>

                                    <!-- Requested Quantity -->
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Requested Quantity:</span>
                                        <span class="font-medium text-blue-600">
                                            {{ $exchange->requested_quantity ?? $exchange->product->quantity }} {{ $exchange->product->unit }}
                                        </span>
                                    </div>

                                    <!-- Exchanged Quantity (if completed) -->
                                    @if($exchange->status == 'completed' && $exchange->exchanged_quantity)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Exchanged Quantity:</span>
                                        <span class="font-medium text-green-600">
                                            {{ $exchange->exchanged_quantity }} {{ $exchange->product->unit }}
                                        </span>
                                    </div>
                                    @endif

                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Condition:</span>
                                        <span class="font-medium capitalize">{{ $exchange->product->condition }}</span>
                                    </div>

                                    <!-- Price Information -->
                                    @if(!$exchange->product->is_free)
                                    <div class="border-t pt-2 mt-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Unit Price:</span>
                                            <span class="font-medium">रू {{ number_format($exchange->product->price, 2) }}</span>
                                        </div>

                                        <!-- Requested Total Price -->
                                        @php
                                        $requestedQuantity = $exchange->requested_quantity ?? $exchange->product->quantity;
                                        $requestedTotalPrice = $exchange->product->price * $requestedQuantity;
                                        @endphp
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Requested Total:</span>
                                            <span class="font-medium text-blue-600">रू {{ number_format($requestedTotalPrice, 2) }}</span>
                                        </div>

                                        <!-- Exchanged Total Price -->
                                        @if($exchange->status == 'completed' && $exchange->exchanged_quantity)
                                        @php
                                        $exchangedTotalPrice = $exchange->product->price * $exchange->exchanged_quantity;
                                        @endphp
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Exchanged Total:</span>
                                            <span class="font-medium text-green-600">रू {{ number_format($exchangedTotalPrice, 2) }}</span>
                                        </div>
                                        @endif

                                        <!-- Agreed Price (if different) -->
                                        @if($exchange->agreed_price && $exchange->agreed_price != $requestedTotalPrice)
                                        <div class="flex justify-between bg-yellow-50 p-2 rounded mt-1">
                                            <span class="text-gray-500">Agreed Price:</span>
                                            <span class="font-medium text-green-600">रू {{ number_format($exchange->agreed_price, 2) }}</span>
                                        </div>
                                        @endif
                                    </div>
                                    @else
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Price:</span>
                                        <span class="font-medium text-green-600">FREE</span>
                                    </div>
                                    @endif
                                </div>

                                <!-- Quantity Progress Bar (for visual representation) -->
                                @if($exchange->status == 'completed' && $exchange->exchanged_quantity)
                                <div class="mt-4">
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>Quantity Progress</span>
                                        <span>{{ $exchange->exchanged_quantity }}/{{ $exchange->requested_quantity ?? $exchange->product->quantity }} {{ $exchange->product->unit }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        @php
                                        $requestedQty = $exchange->requested_quantity ?? $exchange->product->quantity;
                                        $exchangedQty = $exchange->exchanged_quantity;
                                        $percentage = min(100, ($exchangedQty / $requestedQty) * 100);
                                        @endphp
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Exchange Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Exchange Information</h3>
                            <div class="space-y-3 sm:space-y-4">
                                <!-- Parties Involved -->
                                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                    <div class="text-center p-3 sm:p-4 border border-gray-200 rounded-lg">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 sm:w-12 sm:h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                            <span class="text-white font-semibold text-xs sm:text-sm">
                                                {{ substr($exchange->fromUser->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <p class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $exchange->fromUser->name }}</p>
                                        <p class="text-xs sm:text-sm text-gray-500">Requester</p>
                                        <!-- Requested Quantity for Requester -->
                                        <div class="mt-2 text-xs text-blue-600 font-medium">
                                            Requested: {{ $exchange->requested_quantity ?? $exchange->product->quantity }} {{ $exchange->product->unit }}
                                        </div>
                                        @if(!$exchange->product->is_free)
                                        <div class="mt-1 text-xs text-blue-700 font-semibold">
                                            रू {{ number_format($requestedTotalPrice, 2) }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="text-center p-3 sm:p-4 border border-gray-200 rounded-lg">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 sm:w-12 sm:h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                            <span class="text-white font-semibold text-xs sm:text-sm">
                                                {{ substr($exchange->toUser->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <p class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $exchange->toUser->name }}</p>
                                        <p class="text-xs sm:text-sm text-gray-500">Product Owner</p>
                                        <!-- Available Quantity for Owner -->
                                        <div class="mt-2 text-xs text-green-600 font-medium">
                                            Available: {{ $exchange->product->quantity }} {{ $exchange->product->unit }}
                                        </div>
                                        @if(!$exchange->product->is_free)
                                        <div class="mt-1 text-xs text-green-700 font-semibold">
                                            रू {{ number_format($exchange->product->price * $exchange->product->quantity, 2) }}
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Exchange Terms -->
                                <div class="border border-gray-200 rounded-lg p-3 sm:p-4">
                                    <h4 class="font-semibold text-gray-900 mb-2 sm:mb-3 text-sm sm:text-base">Exchange Terms</h4>
                                    <div class="space-y-2 text-xs sm:text-sm">
                                        <!-- Price Summary -->
                                        @if(!$exchange->product->is_free)
                                        <div class="bg-green-50 p-2 rounded mb-2">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-green-700 font-medium">Price Summary:</span>
                                            </div>
                                            <div class="space-y-1 text-xs">
                                                <div class="flex justify-between">
                                                    <span class="text-green-600">Unit Price:</span>
                                                    <span class="font-semibold">रू {{ number_format($exchange->product->price, 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-blue-600">Requested Total:</span>
                                                    <span class="font-semibold text-blue-700">रू {{ number_format($requestedTotalPrice, 2) }}</span>
                                                </div>
                                                @if($exchange->status == 'completed' && $exchange->exchanged_quantity)
                                                <div class="flex justify-between">
                                                    <span class="text-green-600">Exchanged Total:</span>
                                                    <span class="font-semibold text-green-700">रू {{ number_format($exchangedTotalPrice, 2) }}</span>
                                                </div>
                                                @endif
                                                @if($exchange->agreed_price && $exchange->agreed_price != $requestedTotalPrice)
                                                <div class="flex justify-between bg-yellow-100 p-1 rounded">
                                                    <span class="text-orange-600">Agreed Price:</span>
                                                    <span class="font-semibold text-orange-700">रू {{ number_format($exchange->agreed_price, 2) }}</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @else
                                        <div class="bg-green-50 p-2 rounded mb-2">
                                            <div class="text-center">
                                                <span class="text-green-700 font-medium text-sm">FREE EXCHANGE</span>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Quantity Summary -->
                                        <div class="bg-blue-50 p-2 rounded mb-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-blue-700 font-medium">Quantity Summary:</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 mt-1 text-xs">
                                                <div class="text-center">
                                                    <div class="text-blue-600 font-semibold">{{ $exchange->requested_quantity ?? $exchange->product->quantity }} {{ $exchange->product->unit }}</div>
                                                    <div class="text-blue-500">Requested</div>
                                                </div>
                                                @if($exchange->status == 'completed' && $exchange->exchanged_quantity)
                                                <div class="text-center">
                                                    <div class="text-green-600 font-semibold">{{ $exchange->exchanged_quantity }} {{ $exchange->product->unit }}</div>
                                                    <div class="text-green-500">Exchanged</div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Type:</span>
                                            <span class="font-medium capitalize">{{ $exchange->type }}</span>
                                        </div>

                                        @if($exchange->agreed_price)
                                        <div class="flex justify-between bg-yellow-50 p-2 rounded">
                                            <span class="text-gray-500">Final Agreed Price:</span>
                                            <span class="font-medium text-green-600 text-sm">रू {{ number_format($exchange->agreed_price, 2) }}</span>
                                        </div>
                                        @endif

                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Requested:</span>
                                            <span class="font-medium text-right">{{ $exchange->created_at->format('M j, Y') }}</span>
                                        </div>
                                        @if($exchange->exchange_date)
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Response Deadline:</span>
                                            <span class="font-medium text-right">{{ $exchange->exchange_date->format('M j, Y') }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Message -->
                                <div class="border border-gray-200 rounded-lg p-3 sm:p-4">
                                    <h4 class="font-semibold text-gray-900 mb-2 text-sm sm:text-base">Message from Requester</h4>
                                    <p class="text-gray-700 bg-gray-50 p-2 sm:p-3 rounded text-sm sm:text-base">"{{ $exchange->message }}"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                            <a href="{{ route('exchanges.my') }}"
                                class="text-blue-600 hover:text-blue-900 font-medium text-sm sm:text-base order-2 sm:order-1 text-center sm:text-left">
                                ← Back to My Exchanges
                            </a>

                            <div class="flex flex-col sm:flex-row gap-2 sm:space-x-3 order-1 sm:order-2">
                                @if($exchange->status == 'pending')
                                @if(Auth::id() == $exchange->to_user_id)
                                <!-- Seller Actions -->
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <form action="{{ route('exchanges.accept', $exchange) }}" method="POST" class="flex-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full bg-green-500 text-white px-4 sm:px-6 py-2 rounded-md hover:bg-green-600 transition-colors text-sm sm:text-base min-h-[44px]">
                                            Accept Request
                                        </button>
                                    </form>
                                    <form action="{{ route('exchanges.cancel', $exchange) }}" method="POST" class="flex-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full bg-red-500 text-white px-4 sm:px-6 py-2 rounded-md hover:bg-red-600 transition-colors text-sm sm:text-base min-h-[44px]">
                                            Decline
                                        </button>
                                    </form>
                                </div>
                                @else
                                <!-- Requester Actions -->
                                <form action="{{ route('exchanges.cancel', $exchange) }}" method="POST" class="w-full">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="w-full bg-red-500 text-white px-4 sm:px-6 py-2 rounded-md hover:bg-red-600 transition-colors text-sm sm:text-base min-h-[44px]">
                                        Cancel Request
                                    </button>
                                </form>
                                @endif
                                @elseif($exchange->status == 'accepted')
                                <!-- Both parties can mark as complete -->
                                <div class="flex flex-col sm:flex-row gap-2 w-full">
                                    <form action="{{ route('exchanges.complete', $exchange) }}" method="POST" class="flex-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full bg-green-500 text-white px-4 sm:px-6 py-2 rounded-md hover:bg-green-600 transition-colors text-sm sm:text-base min-h-[44px]">
                                            Mark as Completed
                                        </button>
                                    </form>
                                    <form action="{{ route('exchanges.cancel', $exchange) }}" method="POST" class="flex-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full bg-red-500 text-white px-4 sm:px-6 py-2 rounded-md hover:bg-red-600 transition-colors text-sm sm:text-base min-h-[44px]">
                                            Cancel Exchange
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Exchange Timeline -->
                    <div class="mt-6 sm:mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Exchange Timeline</h3>
                        <div class="space-y-3 sm:space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-white text-xs sm:text-sm">1</span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 text-sm sm:text-base">Request Sent</p>
                                    <p class="text-xs sm:text-sm text-gray-500">{{ $exchange->created_at->format('M j, Y g:i A') }}</p>
                                    <p class="text-xs sm:text-sm text-gray-600 mt-1">
                                        {{ $exchange->fromUser->name }} requested
                                        <strong>{{ $exchange->requested_quantity ?? $exchange->product->quantity }} {{ $exchange->product->unit }}</strong>
                                        @if(!$exchange->product->is_free)
                                        for <strong>रू {{ number_format($requestedTotalPrice, 2) }}</strong>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if($exchange->status != 'pending')
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-white text-xs sm:text-sm">2</span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 text-sm sm:text-base">Request Accepted</p>
                                    <p class="text-xs sm:text-sm text-gray-500">{{ $exchange->updated_at->format('M j, Y g:i A') }}</p>
                                    <p class="text-xs sm:text-sm text-gray-600 mt-1">
                                        {{ $exchange->toUser->name }} accepted the request for
                                        <strong>{{ $exchange->requested_quantity ?? $exchange->product->quantity }} {{ $exchange->product->unit }}</strong>
                                        @if($exchange->agreed_price)
                                        at <strong>रू {{ number_format($exchange->agreed_price, 2) }}</strong>
                                        @elseif(!$exchange->product->is_free)
                                        for <strong>रू {{ number_format($requestedTotalPrice, 2) }}</strong>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @endif

                            @if($exchange->status == 'completed')
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="text-white text-xs sm:text-sm">3</span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 text-sm sm:text-base">Exchange Completed</p>
                                    <p class="text-xs sm:text-sm text-gray-500">{{ $exchange->updated_at->format('M j, Y g:i A') }}</p>
                                    <p class="text-xs sm:text-sm text-gray-600 mt-1">
                                        <strong>{{ $exchange->exchanged_quantity ?? $exchange->requested_quantity }} {{ $exchange->product->unit }}</strong>
                                        successfully exchanged
                                        @if($exchange->agreed_price)
                                        for <strong>रू {{ number_format($exchange->agreed_price, 2) }}</strong>
                                        @elseif(!$exchange->product->is_free)
                                        for <strong>रू {{ number_format($exchangedTotalPrice ?? $requestedTotalPrice, 2) }}</strong>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .transition-colors {
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
        }

        @media (max-width: 640px) {
            .text-right {
                text-align: left;
            }
        }
    </style>
</x-app-layout>