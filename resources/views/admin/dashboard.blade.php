@extends('layouts.admin')

@section('title', 'Admin Dashboard - Fraud Management')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Fraud Management Dashboard</h1>
    <p class="text-gray-600">Monitor and manage fraudulent activities</p>
</div>

<!-- Fraud Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- High Risk Users -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
                <span class="text-red-600 text-2xl">⚠️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">High Risk Users</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['high_risk_users'] ?? 0 }}</p>
                <p class="text-xs text-red-600 mt-1">3+ strikes</p>
            </div>
        </div>
    </div>

    <!-- Fraud Flags Today -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
        <div class="flex items-center">
            <div class="p-3 bg-orange-100 rounded-lg">
                <span class="text-orange-600 text-2xl">🚩</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Fraud Flags Today</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['fraud_flags_today'] ?? 0 }}</p>
                <p class="text-xs text-orange-600 mt-1">Last 24 hours</p>
            </div>
        </div>
    </div>

    <!-- Suspended Users -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
                <span class="text-yellow-600 text-2xl">⏸️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Suspended Users</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['suspended_users'] ?? 0 }}</p>
                <p class="text-xs text-yellow-600 mt-1">Active suspensions</p>
            </div>
        </div>
    </div>

    <!-- Fraud Products -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-lg">
                <span class="text-purple-600 text-2xl">🕵️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Fraud Products</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['fraud_products'] ?? 0 }}</p>
                <p class="text-xs text-purple-600 mt-1">Deleted for fraud</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- High Risk Users -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <span class="text-red-500 mr-2">⚠️</span>
                High Risk Users
                <span class="ml-2 bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">
                    {{ $highRiskUsers->count() }}
                </span>
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($highRiskUsers as $user)
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="text-red-600 font-semibold">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $user->name }}</p>
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <span>{{ $user->email }}</span>
                                <span>•</span>
                                <span class="font-semibold text-red-600">{{ $user->strike_count }} strikes</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                @if($user->risk_level === 'critical') bg-red-100 text-red-800
                                @elseif($user->risk_level === 'high') bg-orange-100 text-orange-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($user->risk_level) }}
                            </span>
                            <a href="{{ route('admin.users.detail', $user) }}"
                                class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                View
                            </a>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Score: {{ $user->fraud_score }}/100</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <span class="text-4xl">🎉</span>
                    <p class="text-gray-500 mt-2">No high-risk users found</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Fraud Activities -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <span class="text-orange-500 mr-2">🚩</span>
                Recent Fraud Activities
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($fraudActivities as $activity)
                <div class="flex items-start space-x-3 p-3 bg-orange-50 rounded-lg border border-orange-200">
                    <div class="flex-shrink-0 w-2 h-2 bg-orange-500 rounded-full mt-2"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <span class="font-medium">{{ $activity->user->name ?? 'System' }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $activity->created_at->diffForHumans() }}</span>
                            <span class="mx-2">•</span>
                            <span class="px-1 py-0.5 bg-orange-100 text-orange-800 rounded text-xs">
                                {{ ucfirst($activity->action) }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <span class="text-4xl">📊</span>
                    <p class="text-gray-500 mt-2">No fraud activities detected</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <a href="{{ route('admin.users') }}?status=high_risk"
            class="p-4 border border-red-200 rounded-lg text-center hover:bg-red-50 transition-colors">
            <span class="text-2xl block mb-2">🔍</span>
            <p class="font-medium text-gray-900">Review High Risk</p>
            <p class="text-sm text-gray-500">{{ $stats['high_risk_users'] ?? 0 }} users</p>
        </a>

        <a href="{{ route('admin.user-activities') }}?fraud_only=1"
            class="p-4 border border-orange-200 rounded-lg text-center hover:bg-orange-50 transition-colors">
            <span class="text-2xl block mb-2">📋</span>
            <p class="font-medium text-gray-900">Fraud Logs</p>
            <p class="text-sm text-gray-500">View all activities</p>
        </a>

        <a href="{{ route('admin.deleted-products') }}?fraud_only=1"
            class="p-4 border border-purple-200 rounded-lg text-center hover:bg-purple-50 transition-colors">
            <span class="text-2xl block mb-2">🕵️</span>
            <p class="font-medium text-gray-900">Fraud Products</p>
            <p class="text-sm text-gray-500">{{ $stats['fraud_products'] ?? 0 }} items</p>
        </a>

        <a href="{{ route('admin.deleted-users') }}"
            class="p-4 border border-gray-200 rounded-lg text-center hover:bg-gray-50 transition-colors">
            <span class="text-2xl block mb-2">🚫</span>
            <p class="font-medium text-gray-900">Banned Users</p>
            <p class="text-sm text-gray-500">{{ $stats['banned_users'] ?? 0 }} accounts</p>
        </a>
    </div>
</div>
@endsection