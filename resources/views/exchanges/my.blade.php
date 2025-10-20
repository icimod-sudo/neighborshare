<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Exchanges') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button onclick="showTab('received')"
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm"
                            id="receivedTab">
                            Requests Received
                        </button>
                        <button onclick="showTab('sent')"
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm"
                            id="sentTab">
                            Requests Sent
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Received Exchanges -->
            <div id="receivedTabContent" class="tab-content">
                @if($receivedExchanges->count() > 0)
                <div class="space-y-4">
                    @foreach($receivedExchanges as $exchange)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-3">
                                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                            <span class="text-white font-semibold text-sm">
                                                {{ substr($exchange->fromUser->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $exchange->fromUser->name }} wants your item
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                Requested {{ $exchange->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="ml-13">
                                        <p class="text-gray-700 mb-2">
                                            <strong>Item:</strong> {{ $exchange->product->title }}
                                        </p>
                                        <p class="text-gray-700 mb-2">
                                            <strong>Message:</strong> "{{ $exchange->message }}"
                                        </p>
                                        <p class="text-gray-700">
                                            <strong>Type:</strong>
                                            <span class="capitalize">{{ $exchange->type }}</span>
                                            @if($exchange->agreed_price)
                                            - ₹{{ $exchange->agreed_price }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                @if($exchange->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($exchange->status == 'accepted') bg-blue-100 text-blue-800
                                                @elseif($exchange->status == 'completed') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                        {{ ucfirst($exchange->status) }}
                                    </span>
                                    <!-- In the received exchanges section, add this after the status badge -->
                                    <div class="mt-2">
                                        <a href="{{ route('exchanges.show', $exchange) }}"
                                            class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                            View Details
                                        </a>
                                    </div>
                                    <!-- Actions -->
                                    <div class="mt-3 space-x-2">
                                        @if($exchange->status == 'pending')
                                        <form action="{{ route('exchanges.accept', $exchange) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-green-500  px-3 py-1 rounded text-sm hover:bg-green-600" >
                                                Accept
                                            </button>
                                        </form>
                                        <form action="{{ route('exchanges.cancel', $exchange) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-red-500  px-3 py-1 rounded text-sm hover:bg-red-600">
                                                Decline
                                            </button>
                                        </form>
                                        @elseif($exchange->status == 'accepted')
                                        <form action="{{ route('exchanges.complete', $exchange) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-blue-500  px-3 py-1 rounded text-sm hover:bg-blue-600">
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
                    <div class="p-12 text-center">
                        <div class="text-gray-400 text-6xl mb-4">📨</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No requests received yet</h3>
                        <p class="text-gray-500">When people request your items, they'll appear here.</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sent Exchanges -->
            <div id="sentTabContent" class="tab-content hidden">
                @if($sentExchanges->count() > 0)
                <div class="space-y-4">
                    @foreach($sentExchanges as $exchange)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-3">
                                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                            <span class="text-white font-semibold text-sm">
                                                {{ substr($exchange->toUser->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                You requested from {{ $exchange->toUser->name }}
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                Sent {{ $exchange->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="ml-13">
                                        <p class="text-gray-700 mb-2">
                                            <strong>Item:</strong> {{ $exchange->product->title }}
                                        </p>
                                        <p class="text-gray-700 mb-2">
                                            <strong>Your Message:</strong> "{{ $exchange->message }}"
                                        </p>
                                        <p class="text-gray-700">
                                            <strong>Type:</strong>
                                            <span class="capitalize">{{ $exchange->type }}</span>
                                            @if($exchange->agreed_price)
                                            - ₹{{ $exchange->agreed_price }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                @if($exchange->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($exchange->status == 'accepted') bg-blue-100 text-blue-800
                                                @elseif($exchange->status == 'completed') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                        {{ ucfirst($exchange->status) }}
                                    </span>
                                    <!-- In the sent exchanges section, add this after the status badge -->
                                    <div class="mt-2">
                                        <a href="{{ route('exchanges.show', $exchange) }}"
                                            class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                            View Details
                                        </a>
                                    </div>

                                    <!-- Actions -->
                                    @if($exchange->status == 'pending')
                                    <div class="mt-3">
                                        <form action="{{ route('exchanges.cancel', $exchange) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
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
                    <div class="p-12 text-center">
                        <div class="text-gray-400 text-6xl mb-4">📤</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No requests sent yet</h3>
                        <p class="text-gray-500">When you request items from others, they'll appear here.</p>
                        <a href="{{ route('products.index') }}"
                            class="inline-block mt-4 bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600">
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
    </script>
</x-app-layout>