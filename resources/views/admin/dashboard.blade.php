@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
    <p class="text-gray-600">Welcome to the admin panel</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
                <span class="text-blue-600 text-2xl">👥</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Users</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_users'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
                <span class="text-green-600 text-2xl">📦</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Products</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_products'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-lg">
                <span class="text-purple-600 text-2xl">🔄</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Exchanges</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_exchanges'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
                <span class="text-yellow-600 text-2xl">⏳</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Pending Exchanges</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_exchanges'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Activities -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>
            <div class="space-y-4">
                @forelse($recentActivities as $activity)
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <span>{{ $activity->user->name }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No recent activities</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Popular Categories -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Categories</h3>
            <div class="space-y-3">
                @foreach($popularCategories as $category)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 capitalize">{{ $category->category }}</span>
                    <div class="flex items-center">
                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                            <div class="bg-green-500 h-2 rounded-full"
                                style="width: {{ ($category->count / max(1, $stats['total_products'])) * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $category->count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection