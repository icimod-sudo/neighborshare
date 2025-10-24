@extends('layouts.admin')

@section('title', 'User Details - ' . $user->name)

@section('content')

<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Details</h1>
            <p class="text-gray-600">Comprehensive view of user account and activities</p>
        </div>
        <a href="{{ route('admin.users') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
            <span class="mr-2">←</span>
            Back to Users
        </a>
    </div>
</div>

<!-- User Overview -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- User Profile Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-blue-600 font-semibold text-2xl">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
            </div>
            <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
            <p class="text-gray-600">{{ $user->email }}</p>
            @if($user->phone)
            <p class="text-gray-500 mt-1">{{ $user->phone }}</p>
            @endif

            <!-- Status Badge -->
            <div class="mt-3">
                @if($user->trashed())
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    🚫 Banned
                </span>
                @elseif($user->suspended_until && $user->suspended_until->isFuture())
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    ⏸️ Suspended until {{ $user->suspended_until->format('M j, Y') }}
                </span>
                @elseif(!$user->is_active)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                    ❌ Inactive
                </span>
                @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    ✅ Active
                </span>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">{{ $user->products->count() }}</p>
                <p class="text-sm text-gray-600">Products</p>
            </div>
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">
                    {{ ($user->sentExchanges->count() ?? 0) + ($user->receivedExchanges->count() ?? 0) }}
                </p>
                <p class="text-sm text-gray-600">Exchanges</p>
            </div>
        </div>

        <!-- Fraud Risk -->
        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Fraud Risk Score</span>
                <span class="text-sm font-bold 
                    @if($riskLevel === 'critical') text-red-600
                    @elseif($riskLevel === 'high') text-orange-600
                    @elseif($riskLevel === 'medium') text-yellow-600
                    @else text-green-600 @endif">
                    {{ ucfirst($riskLevel) }}
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="h-2 rounded-full 
                    @if($riskLevel === 'critical') bg-red-600
                    @elseif($riskLevel === 'high') bg-orange-600
                    @elseif($riskLevel === 'medium') bg-yellow-600
                    @else bg-green-600 @endif"
                    style="width: {{ $fraudScore }}%">
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 text-right">Score: {{ $fraudScore }}/100</p>
        </div>

        <!-- Strike Count -->
        <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
            <span class="text-sm font-medium text-red-700">Strikes</span>
            <span class="text-lg font-bold text-red-600">{{ $user->strike_count ?? 0 }}</span>
        </div>
    </div>

    <!-- Account Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span class="mr-2">📋</span>
            Account Information
        </h3>

        <div class="space-y-3">
            <div>
                <label class="text-sm font-medium text-gray-500">User ID</label>
                <p class="text-sm text-gray-900">{{ $user->id }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-500">Member Since</label>
                <p class="text-sm text-gray-900">{{ $user->created_at->format('M j, Y') }}</p>
            </div>


            <div>
                <label class="text-sm font-medium text-gray-500">Email Verified</label>
                <p class="text-sm">
                    @if($user->email_verified_at)
                    <span class="text-green-600">✅ Verified</span>
                    @else
                    <span class="text-red-600">❌ Not Verified</span>
                    @endif
                </p>
            </div>

            @if($user->location)
            <div>
                <label class="text-sm font-medium text-gray-500">Location</label>
                <p class="text-sm text-gray-900">{{ $user->location }}</p>
            </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 space-y-2">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Quick Actions</h4>

            @if($user->trashed())
            <form action="{{ route('admin.users.restore', $user) }}" method="POST" class="inline-block w-full">
                @csrf
                <button type="submit"
                    onclick="return confirm('Restore this user account?')"
                    class="w-full text-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    ↩️ Restore User
                </button>
            </form>
            @else
            <div class="grid grid-cols-2 gap-2">
                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        {{ $user->is_active ? '❌ Deactivate' : '✅ Activate' }}
                    </button>
                </form>

                <button onclick="openSuspensionModal()"
                    class="w-full px-3 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                    ⏸️ Suspend
                </button>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <form action="{{ route('admin.users.add-strike', $user) }}" method="POST">
                    @csrf
                    <button type="submit"
                        onclick="return confirm('Add a strike to this user?')"
                        class="w-full px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                        ⚠️ Add Strike
                    </button>
                </form>

                <button onclick="openBanModal()"
                    class="w-full px-3 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 transition-colors text-sm">
                    🚫 Ban User
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Fraud Flags -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span class="mr-2">🚩</span>
            Recent Fraud Flags
        </h3>

        @if(count($recentFlags) > 0)
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @foreach($recentFlags as $flag)
            <div class="p-3 border border-red-200 rounded-lg bg-red-50">
                <div class="flex justify-between items-start mb-1">
                    <span class="text-sm font-medium text-red-800 capitalize">
                        {{ str_replace('_', ' ', $flag['type'] ?? 'unknown') }}
                    </span>
                    <span class="text-xs text-red-600">
                        {{ \Carbon\Carbon::parse($flag['created_at'])->diffForHumans() }}
                    </span>
                </div>
                <p class="text-sm text-red-700">{{ $flag['details'] ?? 'No details provided' }}</p>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <span class="text-4xl">🎉</span>
            <p class="text-gray-500 mt-2">No fraud flags</p>
        </div>
        @endif

        <!-- Add Fraud Flag Form -->
        <form action="{{ route('admin.users.add-fraud-flag', $user) }}" method="POST" class="mt-4">
            @csrf
            <div class="space-y-3">
                <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select flag type...</option>
                    <option value="fake_product">Fake Product</option>
                    <option value="payment_issue">Payment Issue</option>
                    <option value="harassment">Harassment</option>
                    <option value="spam">Spam</option>
                    <option value="other">Other</option>
                </select>

                <textarea name="details"
                    placeholder="Flag details..."
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    rows="2"></textarea>

                <button type="submit"
                    class="w-full px-3 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                    🚩 Add Fraud Flag
                </button>
            </div>
        </form>
    </div>
</div>

<!-- User's Products -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <span class="mr-2">🎁</span>
            User's Products ({{ $user->products->count() }})
        </h3>
    </div>

    <div class="p-6">
        @if($user->products->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($user->products as $product)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-medium text-gray-900">{{ $product->title }}</h4>
                    <span class="text-xs px-2 py-1 rounded 
                            @if($product->is_available) bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                        {{ $product->is_available ? 'Available' : 'Unavailable' }}
                    </span>
                </div>

                <p class="text-sm text-gray-600 mb-2">{{ Str::limit($product->description, 80) }}</p>

                <div class="flex justify-between items-center text-xs text-gray-500">
                    <span>Category: {{ ucfirst($product->category) }}</span>
                    <span>Exchanges: {{ $product->exchanges_count ?? 0 }}</span>
                </div>

                @if($product->trashed())
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                        🗑️ Deleted
                    </span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <span class="text-4xl">📦</span>
            <p class="text-gray-500 mt-2">No products listed</p>
        </div>
        @endif
    </div>
</div>

<!-- Modals -->
<!-- Suspension Modal -->
<div id="suspensionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Suspend User</h3>

            <form action="{{ route('admin.users.suspend', $user) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Suspension Period</label>
                    <select name="suspension_days" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="1">1 Day</option>
                        <option value="3">3 Days</option>
                        <option value="7">7 Days</option>
                        <option value="30">30 Days</option>
                        <option value="90">90 Days</option>
                        <option value="365">1 Year</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                    <textarea name="reason" required
                        placeholder="Enter suspension reason..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        rows="3"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                        onclick="closeSuspensionModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                        Suspend User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ban Modal -->
<div id="banModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ban User</h3>

            <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ban Reason</label>
                    <textarea name="reason" required
                        placeholder="Enter ban reason..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        rows="3"></textarea>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-red-700">
                        ⚠️ This will permanently ban the user and remove all their products.
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                        onclick="closeBanModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        🚫 Ban User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openSuspensionModal() {
        document.getElementById('suspensionModal').classList.remove('hidden');
    }

    function closeSuspensionModal() {
        document.getElementById('suspensionModal').classList.add('hidden');
    }

    function openBanModal() {
        document.getElementById('banModal').classList.remove('hidden');
    }

    function closeBanModal() {
        document.getElementById('banModal').classList.add('hidden');
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const suspensionModal = document.getElementById('suspensionModal');
        const banModal = document.getElementById('banModal');

        if (event.target === suspensionModal) {
            closeSuspensionModal();
        }
        if (event.target === banModal) {
            closeBanModal();
        }
    }
</script>
@endsection