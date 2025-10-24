@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Activity Logs</h1>
            <p class="text-gray-600">Monitor all system activities and user actions</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.fraud.reports') }}"
                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                Fraud Reports
            </a>
            <a href="{{ route('admin.dashboard') }}"
                class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form action="{{ route('admin.activity-logs') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
            <select name="date" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">All Time</option>
                <option value="today" {{ request('date') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('date') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('date') == 'month' ? 'selected' : '' }}>This Month</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
            <select name="action" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">All Actions</option>
                <option value="suspended" {{ request('action') == 'suspended' ? 'selected' : '' }}>Suspensions</option>
                <option value="banned" {{ request('action') == 'banned' ? 'selected' : '' }}>Bans</option>
                <option value="strike_added" {{ request('action') == 'strike_added' ? 'selected' : '' }}>Strikes</option>
                <option value="fraud_flag_added" {{ request('action') == 'fraud_flag_added' ? 'selected' : '' }}>Fraud Flags</option>
                <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Creations</option>
                <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updates</option>
                <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deletions</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fraud Only</label>
            <select name="fraud_only" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">All Activities</option>
                <option value="1" {{ request('fraud_only') == '1' ? 'selected' : '' }}>Fraud Related Only</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Activity Logs Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                System Activities ({{ $activities->total() }})
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($activities as $activity)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($activity->causer)
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 text-sm font-medium">
                                    {{ substr($activity->causer->name, 0, 1) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $activity->causer->name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $activity->causer->email }}
                                </div>
                            </div>
                            @else
                            <div class="text-sm text-gray-500">System</div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                        $event = $activity->event ?? 'created';
                        $description = $activity->description ?? '';

                        // Extract action from description for Fraud Control actions
                        if (str_contains($description, 'Fraud Control')) {
                        $event = explode(' ', $description)[2] ?? $event; // Get the action from "Fraud Control: suspended on user..."
                        }
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            @if(in_array($event, ['suspended', 'banned', 'strike_added', 'strike_removed', 'fraud_flag_added'])) 
                                bg-red-100 text-red-800
                            @elseif(in_array($event, ['created', 'restored', 'unsuspended']))
                                bg-green-100 text-green-800
                            @elseif(in_array($event, ['updated']))
                                bg-blue-100 text-blue-800
                            @elseif(in_array($event, ['deleted']))
                                bg-gray-100 text-gray-800
                            @else
                                bg-yellow-100 text-yellow-800
                            @endif">
                            {{ str_replace('_', ' ', $event) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-md">
                            {{ $activity->description }}
                        </div>
                        @if($activity->subject_type && $activity->subject_id)
                        <div class="text-xs text-gray-500 mt-1">
                            {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($activity->subject)
                        {{ $activity->subject->name ?? $activity->subject->email ?? $activity->subject->title ?? 'N/A' }}
                        @else
                        N/A
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $activity->properties['ip'] ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div>{{ $activity->created_at->format('M j, Y') }}</div>
                        <div class="text-gray-400">{{ $activity->created_at->format('g:i A') }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center">
                        <div class="text-gray-400">
                            <span class="text-4xl">📝</span>
                            <p class="mt-2 text-lg font-medium text-gray-900">No activities found</p>
                            <p class="text-gray-500">There are no activity logs matching your criteria.</p>
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

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
                <span class="text-red-600 text-2xl">🚩</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Fraud Activities Today</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ \Spatie\Activitylog\Models\Activity::where('description', 'like', '%Fraud Control%')->whereDate('created_at', today())->count() }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-orange-100 rounded-lg">
                <span class="text-orange-600 text-2xl">⏸️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Suspensions This Week</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ \Spatie\Activitylog\Models\Activity::where('description', 'like', '%suspended%')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count() }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-lg">
                <span class="text-purple-600 text-2xl">⚠️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Strikes This Month</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ \Spatie\Activitylog\Models\Activity::where('description', 'like', '%strike%')->whereMonth('created_at', now()->month)->count() }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection