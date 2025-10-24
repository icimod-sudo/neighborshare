@extends('layouts.admin')

@section('title', 'High Risk Users')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">High Risk Users</h1>
            <p class="text-gray-600">Users with elevated fraud risk scores</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.users') }}?status=high_risk"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Manage Users
            </a>
        </div>
    </div>
</div>

<!-- High Risk Users Grid -->
<div class="grid grid-cols-1 gap-6">
    @forelse($users as $user)
    <div class="bg-white rounded-lg shadow border-l-4 
        @if($user->risk_level === 'critical') border-red-500 bg-red-50
        @elseif($user->risk_level === 'high') border-orange-500 bg-orange-50
        @else border-yellow-500 bg-yellow-50 @endif">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                        <span class="text-gray-600 font-semibold">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        <div class="flex items-center space-x-4 mt-1">
                            <span class="text-sm text-gray-500">Joined: {{ $user->created_at->format('M j, Y') }}</span>
                            <span class="text-sm text-gray-500">•</span>
                            <span class="text-sm font-semibold text-red-600">{{ $user->strike_count }} strikes</span>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <div class="flex items-center space-x-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold 
                                @if($user->risk_level === 'critical') text-red-600
                                @elseif($user->risk_level === 'high') text-orange-600
                                @else text-yellow-600 @endif">
                                {{ $user->fraud_score }}
                            </div>
                            <div class="text-xs text-gray-500">Risk Score</div>
                        </div>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full 
                            @if($user->risk_level === 'critical') bg-red-100 text-red-800
                            @elseif($user->risk_level === 'high') bg-orange-100 text-orange-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($user->risk_level) }} Risk
                        </span>
                    </div>
                </div>
            </div>

            <!-- User Stats -->
            <div class="grid grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-200">
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $user->products_count }}</div>
                    <div class="text-xs text-gray-500">Products</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $user->sent_exchanges_count }}</div>
                    <div class="text-xs text-gray-500">Sent Exchanges</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ $user->received_exchanges_count }}</div>
                    <div class="text-xs text-gray-500">Received Exchanges</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ is_array($user->fraud_flags) ? count($user->fraud_flags) : 0 }}</div>
                    <div class="text-xs text-gray-500">Fraud Flags</div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 mt-4">
                <a href="{{ route('admin.users.detail', $user) }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition-colors">
                    View Details
                </a>
                <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="bg-orange-600 text-white px-4 py-2 rounded-md text-sm hover:bg-orange-700 transition-colors">
                        Suspend
                    </button>
                </form>
                <form action="{{ route('admin.users.add-strike', $user) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="reason" value="High risk user - manual review">
                    <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700 transition-colors">
                        Add Strike
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <span class="text-6xl">🎉</span>
        <h3 class="text-xl font-semibold text-gray-900 mt-4">No High Risk Users</h3>
        <p class="text-gray-600 mt-2">Great job! There are no users with high fraud risk scores.</p>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($users->hasPages())
<div class="mt-6">
    {{ $users->links() }}
</div>
@endif
@endsection