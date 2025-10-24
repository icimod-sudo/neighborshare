<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Exchanges') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">

            <!-- Tabs -->
            <div class="mb-4 sm:mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-4 sm:space-x-8 overflow-x-auto">
                        <button onclick="showTab('received')"
                            class="tab-button py-3 sm:py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap flex-shrink-0"
                            id="receivedTab">
                            Requests Received
                        </button>
                        <button onclick="showTab('sent')"
                            class="tab-button py-3 sm:py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap flex-shrink-0"
                            id="sentTab">
                            Requests Sent
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Received Exchanges -->
            <div id="receivedTabContent" class="tab-content">
                @if($receivedExchanges->count() > 0)
                <div class="space-y-3 sm:space-y-4">
                    @foreach($receivedExchanges as $exchange)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-4 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                                <div class="flex-1">
                                    <div class="flex items-start space-x-3 mb-3">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-white font-semibold text-xs sm:text-sm">
                                                {{ substr($exchange->fromUser->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 line-clamp-2">
                                                {{ $exchange->fromUser->name }} wants your item
                                            </h3>
                                            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                                                Requested {{ $exchange->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="ml-0 sm:ml-13 space-y-2">
                                        <p class="text-gray-700 text-sm sm:text-base">
                                            <strong class="text-sm">Item:</strong>
                                            <span class="line-clamp-1">{{ $exchange->product->title }}</span>
                                        </p>
                                        <p class="text-gray-700 text-sm sm:text-base">
                                            <strong class="text-sm">Message:</strong>
                                            <span class="line-clamp-2">"{{ $exchange->message }}"</span>
                                        </p>
                                        <p class="text-gray-700 text-sm sm:text-base">
                                            <strong class="text-sm">Type:</strong>
                                            <span class="capitalize">{{ $exchange->type }}</span>
                                            @if($exchange->agreed_price)
                                            - रू {{ $exchange->agreed_price }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-start sm:items-end gap-2 sm:gap-3 mt-3 sm:mt-0">
                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium
                                                @if($exchange->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($exchange->status == 'accepted') bg-blue-100 text-blue-800
                                                @elseif($exchange->status == 'completed') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                        {{ ucfirst($exchange->status) }}
                                    </span>

                                    <!-- View Details Link -->
                                    <div>
                                        <a href="{{ route('exchanges.show', $exchange) }}"
                                            class="text-blue-600 hover:text-blue-900 text-xs sm:text-sm font-medium">
                                            View Details
                                        </a>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-wrap gap-2 sm:space-x-2">
                                        @if($exchange->status == 'pending')
                                        <form action="{{ route('exchanges.accept', $exchange) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-green-500 text-white px-2 sm:px-3 py-1 rounded text-xs sm:text-sm hover:bg-green-600 min-h-[32px] sm:min-h-[36px] flex items-center justify-center">
                                                Accept
                                            </button>
                                        </form>
                                        <form action="{{ route('exchanges.cancel', $exchange) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-red-500 text-white px-2 sm:px-3 py-1 rounded text-xs sm:text-sm hover:bg-red-600 min-h-[32px] sm:min-h-[36px] flex items-center justify-center">
                                                Decline
                                            </button>
                                        </form>
                                        @elseif($exchange->status == 'accepted')
                                        <form action="{{ route('exchanges.complete', $exchange) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-blue-500 text-white px-2 sm:px-3 py-1 rounded text-xs sm:text-sm hover:bg-blue-600 min-h-[32px] sm:min-h-[36px] flex items-center justify-center">
                                                Mark Complete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 sm:p-12 text-center">
                        <div class="text-gray-400 text-4xl sm:text-6xl mb-4">📨</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No requests received yet</h3>
                        <p class="text-gray-500 text-sm sm:text-base">When people request your items, they'll appear here.</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sent Exchanges -->
            <div id="sentTabContent" class="tab-content hidden">
                @if($sentExchanges->count() > 0)
                <div class="space-y-3 sm:space-y-4">
                    @foreach($sentExchanges as $exchange)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-4 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                                <div class="flex-1">
                                    <div class="flex items-start space-x-3 mb-3">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-white font-semibold text-xs sm:text-sm">
                                                {{ substr($exchange->toUser->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 line-clamp-2">
                                                You requested from {{ $exchange->toUser->name }}
                                            </h3>
                                            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                                                Sent {{ $exchange->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="ml-0 sm:ml-13 space-y-2">
                                        <p class="text-gray-700 text-sm sm:text-base">
                                            <strong class="text-sm">Item:</strong>
                                            <span class="line-clamp-1">{{ $exchange->product->title }}</span>
                                        </p>
                                        <p class="text-gray-700 text-sm sm:text-base">
                                            <strong class="text-sm">Your Message:</strong>
                                            <span class="line-clamp-2">"{{ $exchange->message }}"</span>
                                        </p>
                                        <p class="text-gray-700 text-sm sm:text-base">
                                            <strong class="text-sm">Type:</strong>
                                            <span class="capitalize">{{ $exchange->type }}</span>
                                            @if($exchange->agreed_price)
                                            - रू {{ $exchange->agreed_price }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-start sm:items-end gap-2 sm:gap-3 mt-3 sm:mt-0">
                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium
                                                @if($exchange->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($exchange->status == 'accepted') bg-blue-100 text-blue-800
                                                @elseif($exchange->status == 'completed') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                        {{ ucfirst($exchange->status) }}
                                    </span>

                                    <!-- View Details Link -->
                                    <div>
                                        <a href="{{ route('exchanges.show', $exchange) }}"
                                            class="text-blue-600 hover:text-blue-900 text-xs sm:text-sm font-medium">
                                            View Details
                                        </a>
                                    </div>

                                    <!-- Actions -->
                                    @if($exchange->status == 'pending')
                                    <div class="mt-1">
                                        <form action="{{ route('exchanges.cancel', $exchange) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-red-500 text-white px-2 sm:px-3 py-1 rounded text-xs sm:text-sm hover:bg-red-600 min-h-[32px] sm:min-h-[36px] flex items-center justify-center">
                                                Cancel Request
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 sm:p-12 text-center">
                        <div class="text-gray-400 text-4xl sm:text-6xl mb-4">📤</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No requests sent yet</h3>
                        <p class="text-gray-500 text-sm sm:text-base mb-4">When you request items from others, they'll appear here.</p>
                        <a href="{{ route('products.index') }}"
                            class="inline-block bg-green-500 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-md hover:bg-green-600 text-sm sm:text-base min-h-[44px] flex items-center justify-center">
                            Browse Products
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Remove active styles from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });

            // Show selected tab content
            document.getElementById(tabName + 'TabContent').classList.remove('hidden');

            // Add active styles to selected tab
            document.getElementById(tabName + 'Tab').classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            document.getElementById(tabName + 'Tab').classList.add('border-blue-500', 'text-blue-600');
        }

        // Show received tab by default
        showTab('received');

        // Add CSS for line clamping
        const style = document.createElement('style');
        style.textContent = `
            .line-clamp-1 {
                display: -webkit-box;
                -webkit-line-clamp: 1;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .ml-13 {
                margin-left: 3.25rem;
            }
            @media (max-width: 640px) {
                .ml-13 {
                    margin-left: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</x-app-layout>