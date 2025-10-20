<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Exchange Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Exchange Status Banner -->
                    <div class="mb-6 p-4 rounded-lg 
                        @if($exchange->status == 'pending') bg-yellow-50 border border-yellow-200
                        @elseif($exchange->status == 'accepted') bg-blue-50 border border-blue-200
                        @elseif($exchange->status == 'completed') bg-green-50 border border-green-200
                        @else bg-red-50 border border-red-200
                        @endif">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">
                                    @if($exchange->status == 'pending') ⏳
                                    @elseif($exchange->status == 'accepted') ✅
                                    @elseif($exchange->status == 'completed') 🎉
                                    @else ❌
                                    @endif
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold 
                                        @if($exchange->status == 'pending') text-yellow-800
                                        @elseif($exchange->status == 'accepted') text-blue-800
                                        @elseif($exchange->status == 'completed') text-green-800
                                        @else text-red-800
                                        @endif">
                                        Exchange {{ ucfirst($exchange->status) }}
                                    </h3>
                                    <p class="text-sm 
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
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @if($exchange->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($exchange->status == 'accepted') bg-blue-100 text-blue-800
                                @elseif($exchange->status == 'completed') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($exchange->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Product Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Details</h3>
                            <div class="border border-gray-200 rounded-lg p-4">
                                @if($exchange->product->image)
                                <img src="{{ asset('storage/' . $exchange->product->image) }}"
                                    alt="{{ $exchange->product->title }}"
                                    class="w-full h-48 object-cover rounded-lg mb-4">
                                @else
                                <div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center mb-4">
                                    <span class="text-gray-400 text-2xl">📦</span>
                                </div>
                                @endif

                                <h4 class="font-semibold text-gray-900 text-lg">{{ $exchange->product->title }}</h4>
                                <p class="text-gray-600 mb-2">{{ $exchange->product->subcategory }}</p>

                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Quantity:</span>
                                        <span class="font-medium">{{ $exchange->product->quantity }} {{ $exchange->product->unit }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Condition:</span>
                                        <span class="font-medium capitalize">{{ $exchange->product->condition }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Original Price:</span>
                                        <span class="font-medium">
                                            @if($exchange->product->is_free)
                                            FREE
                                            @else
                                            ₹{{ $exchange->product->price }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Exchange Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Exchange Information</h3>
                            <div class="space-y-4">
                                <!-- Parties Involved -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                            <span class="text-white font-semibold text-sm">
                                                {{ substr($exchange->fromUser->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <p class="font-semibold text-gray-900">{{ $exchange->fromUser->name }}</p>
                                        <p class="text-sm text-gray-500">Requester</p>
                                    </div>
                                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                            <span class="text-white font-semibold text-sm">
                                                {{ substr($exchange->toUser->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <p class="font-semibold text-gray-900">{{ $exchange->toUser->name }}</p>
                                        <p class="text-sm text-gray-500">Product Owner</p>
                                    </div>
                                </div>

                                <!-- Exchange Terms -->
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-gray-900 mb-3">Exchange Terms</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Type:</span>
                                            <span class="font-medium capitalize">{{ $exchange->type }}</span>
                                        </div>
                                        @if($exchange->agreed_price)
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Agreed Price:</span>
                                            <span class="font-medium text-green-600">₹{{ $exchange->agreed_price }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Requested:</span>
                                            <span class="font-medium">{{ $exchange->created_at->format('M j, Y g:i A') }}</span>
                                        </div>
                                        @if($exchange->exchange_date)
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Response Deadline:</span>
                                            <span class="font-medium">{{ $exchange->exchange_date->format('M j, Y g:i A') }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Message -->
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-gray-900 mb-2">Message from Requester</h4>
                                    <p class="text-gray-700 bg-gray-50 p-3 rounded">"{{ $exchange->message }}"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <a href="{{ route('exchanges.my') }}"
                                class="text-blue-600 hover:text-blue-900 font-medium">
                                ← Back to My Exchanges
                            </a>

                            <div class="space-x-3">
                                @if($exchange->status == 'pending')
                                @if(Auth::id() == $exchange->to_user_id)
                                <!-- Seller Actions -->
                                <form action="{{ route('exchanges.accept', $exchange) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition-colors">
                                        Accept Request
                                    </button>
                                </form>
                                <form action="{{ route('exchanges.cancel', $exchange) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600 transition-colors">
                                        Decline
                                    </button>
                                </form>
                                @else
                                <!-- Requester Actions -->
                                <form action="{{ route('exchanges.cancel', $exchange) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600 transition-colors">
                                        Cancel Request
                                    </button>
                                </form>
                                @endif
                                @elseif($exchange->status == 'accepted')
                                <!-- Both parties can mark as complete -->
                                <form action="{{ route('exchanges.complete', $exchange) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition-colors">
                                        Mark as Completed
                                    </button>
                                </form>
                                <form action="{{ route('exchanges.cancel', $exchange) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600 transition-colors">
                                        Cancel Exchange
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Exchange Timeline -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Exchange Timeline</h3>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-white text-sm">1</span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">Request Sent</p>
                                    <p class="text-sm text-gray-500">{{ $exchange->created_at->format('M j, Y g:i A') }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $exchange->fromUser->name }} requested this item</p>
                                </div>
                            </div>

                            @if($exchange->status != 'pending')
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-white text-sm">2</span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">Request Accepted</p>
                                    <p class="text-sm text-gray-500">{{ $exchange->updated_at->format('M j, Y g:i A') }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $exchange->toUser->name }} accepted the request</p>
                                </div>
                            </div>
                            @endif

                            @if($exchange->status == 'completed')
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-white text-sm">3</span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">Exchange Completed</p>
                                    <p class="text-sm text-gray-500">{{ $exchange->updated_at->format('M j, Y g:i A') }}</p>
                                    <p class="text-sm text-gray-600 mt-1">Item successfully exchanged</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>