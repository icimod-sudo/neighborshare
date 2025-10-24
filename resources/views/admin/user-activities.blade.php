@extends('layouts.admin')

@section('title', 'User Activity Logs')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Activity Logs</h1>
            <p class="text-gray-600">Monitor user activities and behavior patterns</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.users') }}"
                class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                Back to Users
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form action="{{ route('admin.user-activities') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
            <select name="user_id" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">All Users</option>
                @foreach($users as $userOption)
                <option value="{{ $userOption->id }}" {{ request('user_id') == $userOption->id ? 'selected' : '' }}>
                    {{ $userOption->name }} ({{ $userOption->email }})
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Activity Type</label>
            <select name="type" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">All Activities</option>
                <option value="login" {{ request('type') == 'login' ? 'selected' : '' }}>Logins</option>
                <option value="logout" {{ request('type') == 'logout' ? 'selected' : '' }}>Logouts</option>
                <option value="product_view" {{ request('type') == 'product_view' ? 'selected' : '' }}>Product Views</option>
                <option value="product_create" {{ request('type') == 'product_create' ? 'selected' : '' }}>Product Creations</option>
                <option value="product_update" {{ request('type') == 'product_update' ? 'selected' : '' }}>Product Updates</option>
                <option value="product_delete" {{ request('type') == 'product_delete' ? 'selected' : '' }}>Product Deletions</option>
                <option value="exchange_request" {{ request('type') == 'exchange_request' ? 'selected' : '' }}>Exchange Requests</option>
                <option value="exchange_accept" {{ request('type') == 'exchange_accept' ? 'selected' : '' }}>Exchange Acceptances</option>
                <option value="exchange_complete" {{ request('type') == 'exchange_complete' ? 'selected' : '' }}>Exchange Completions</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
            <select name="date_range" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="all" {{ request('date_range') == 'all' ? 'selected' : '' }}>All Time</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Device Type</label>
            <select name="device_type" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">All Devices</option>
                <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>Desktop</option>
                <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>Tablet</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Activity Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
                <span class="text-blue-600 text-2xl">📊</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Activities</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $totalActivities }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
                <span class="text-green-600 text-2xl">👥</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Active Users</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $activeUsersCount }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-lg">
                <span class="text-purple-600 text-2xl">📱</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Mobile Users</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $mobileActivities }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-orange-100 rounded-lg">
                <span class="text-orange-600 text-2xl">🔄</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Product Activities</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $productActivities }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Activities Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                Activity Logs ({{ $activities->total() }})
            </h3>
            <span class="text-sm text-gray-500">
                Page {{ $activities->currentPage() }} of {{ $activities->lastPage() }}
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($activities as $activity)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 text-sm font-medium">
                                    {{ substr($activity->user->name, 0, 1) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $activity->user->name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $activity->user->email }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="text-lg mr-2">{{ $activity->getActivityIcon() }}</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 capitalize">
                                {{ str_replace('_', ' ', $activity->type) }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ $activity->description }}</div>
                        @if($activity->metadata)
                        <div class="text-xs text-gray-500 mt-1">
                            @foreach($activity->metadata as $key => $value)
                            @if(!in_array($key, ['product_title']))
                            {{ ucfirst($key) }}: {{ $value }}@if(!$loop->last), @endif
                            @endif
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="text-lg mr-2">{{ $activity->getDeviceIcon() }}</span>
                            <div>
                                <div class="text-sm text-gray-900">{{ $activity->browser }}</div>
                                <div class="text-xs text-gray-500">{{ $activity->platform }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $activity->ip_address }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div>{{ $activity->performed_at->format('M j, Y') }}</div>
                        <div class="text-gray-400">{{ $activity->performed_at->format('g:i A') }}</div>
                        <div class="text-xs text-gray-400">{{ $activity->performed_at->diffForHumans() }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center">
                        <div class="text-gray-400">
                            <span class="text-4xl">📝</span>
                            <p class="mt-2 text-lg font-medium text-gray-900">No activities found</p>
                            <p class="text-gray-500">No user activities match your current filters.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($activities->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $activities->links() }}
    </div>
    @endif
</div>
@endsection