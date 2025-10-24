@extends('layouts.admin')

@section('title', 'Fraud Reports')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Fraud Reports & Analytics</h1>
    <p class="text-gray-600">Comprehensive fraud analysis and reporting</p>
</div>

<!-- Weekly Report -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
                <span class="text-red-600 text-2xl">🚩</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">New Fraud Cases</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $weeklyReport['new_fraud_cases'] }}</p>
                <p class="text-xs text-gray-500 mt-1">This week</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-orange-100 rounded-lg">
                <span class="text-orange-600 text-2xl">⏸️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Users Suspended</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $weeklyReport['users_suspended'] }}</p>
                <p class="text-xs text-gray-500 mt-1">This week</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-lg">
                <span class="text-purple-600 text-2xl">🚫</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Users Banned</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $weeklyReport['users_banned'] }}</p>
                <p class="text-xs text-gray-500 mt-1">This week</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
                <span class="text-yellow-600 text-2xl">⚠️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Strikes Issued</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $weeklyReport['strikes_issued'] }}</p>
                <p class="text-xs text-gray-500 mt-1">This week</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Top Fraud Categories -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Top Fraud Categories</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($fraudCategories as $category)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 capitalize">
                        {{ \Illuminate\Support\Str::limit($category->deleted_reason, 30) }}
                    </span>
                    <div class="flex items-center">
                        <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                            <div class="bg-red-500 h-2 rounded-full"
                                style="width: {{ ($category->count / max(1, $fraudCategories->sum('count'))) * 100 }}%">
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $category->count }}</span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No fraud categories data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Report Actions</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4">
                <a href="{{ route('admin.activity-logs') }}?fraud_only=1"
                    class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center">
                    <span class="text-2xl block mb-2">📋</span>
                    <p class="font-medium text-gray-900">View Fraud Activities</p>
                    <p class="text-sm text-gray-500">All fraud-related system logs</p>
                </a>

                <a href="{{ route('admin.fraud.high-risk') }}"
                    class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center">
                    <span class="text-2xl block mb-2">⚠️</span>
                    <p class="font-medium text-gray-900">High Risk Users</p>
                    <p class="text-sm text-gray-500">Users requiring immediate attention</p>
                </a>

                <a href="{{ route('admin.deleted-products') }}?fraud_only=1"
                    class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center">
                    <span class="text-2xl block mb-2">🕵️</span>
                    <p class="font-medium text-gray-900">Fraud Products Review</p>
                    <p class="text-sm text-gray-500">Products deleted for fraud</p>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Trends -->
<div class="bg-white rounded-lg shadow mt-6">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Monthly Fraud Trends</h3>
    </div>
    <div class="p-6">
        <div class="text-center py-8 text-gray-500">
            <span class="text-4xl">📊</span>
            <p class="mt-2">Fraud analytics chart would be displayed here</p>
            <p class="text-sm">(Integration with analytics service required)</p>
        </div>
    </div>
</div>
@endsection