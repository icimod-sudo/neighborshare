@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="text-gray-600">Manage all users in the system</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.high-risk-users') }}"
                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                High Risk Users
            </a>
            <a href="{{ route('admin.dashboard') }}"
                class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
@if (session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    {{ session('error') }}
</div>
@endif

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form action="{{ route('admin.users') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Name, email, or phone..."
                class="w-full border-gray-300 rounded-md shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">All Users</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                <option value="high_risk" {{ request('status') == 'high_risk' ? 'selected' : '' }}>High Risk</option>
                <option value="with_strikes" {{ request('status') == 'with_strikes' ? 'selected' : '' }}>With Strikes</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                Filter
            </button>
        </div>

        <div class="flex items-end">
            <a href="{{ route('admin.users') }}" class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors text-center">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                Users ({{ $users->total() }})
            </h3>
            <span class="text-sm text-gray-500">
                Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strikes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-semibold text-sm">
                                    {{ substr($user->name, 0, 1) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $user->name }}
                                    @if($user->deleted_at)
                                    <span class="text-red-500 text-xs">(BANNED)</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                @if($user->phone)
                                <div class="text-xs text-gray-400">{{ $user->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <!-- Status Badges -->
                        @if($user->deleted_at)
                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                            Banned
                        </span>
                        @elseif($user->isSuspended())
                        <span class="px-2 py-1 text-xs font-semibold bg-orange-100 text-orange-800 rounded-full">
                            Suspended
                        </span>
                        <div class="text-xs text-gray-500 mt-1">
                            Until: {{ $user->suspended_until->format('M j, Y') }}
                        </div>
                        @elseif(!$user->is_active)
                        <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full">
                            Inactive
                        </span>
                        @else
                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                            Active
                        </span>
                        @endif

                        <!-- Admin Badge -->
                        @if($user->is_admin)
                        <div class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 rounded-full">
                                Admin
                            </span>
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            @if($user->risk_level === 'critical') bg-red-100 text-red-800
                            @elseif($user->risk_level === 'high') bg-orange-100 text-orange-800
                            @elseif($user->risk_level === 'medium') bg-yellow-100 text-yellow-800
                            @elseif($user->risk_level === 'low') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($user->risk_level) }} Risk
                        </span>
                        <div class="text-xs text-gray-500 mt-1">Score: {{ $user->fraud_score }}/100</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium {{ $user->strike_count > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $user->strike_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $user->products_count }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->created_at->format('M j, Y') }}
                        <div class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex flex-col space-y-2">
                            <!-- View Details -->
                            <a href="{{ route('admin.users.detail', $user) }}"
                                class="text-blue-600 hover:text-blue-900 inline-block">
                                View Details
                            </a>

                            <!-- Toggle Active Status -->
                            @if(!$user->deleted_at)
                            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                    class="text-{{ $user->is_active ? 'orange' : 'green' }}-600 hover:text-{{ $user->is_active ? 'orange' : 'green' }}-900">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            @endif

                            <!-- Quick Actions -->
                            @if(!$user->deleted_at)
                            <div class="flex space-x-2">
                                @if($user->isSuspended())
                                <form action="{{ route('admin.users.unsuspend', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 text-xs">
                                        Unsuspend
                                    </button>
                                </form>
                                @else
                                <!-- Quick Suspend -->
                                <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="suspension_days" value="7">
                                    <input type="hidden" name="reason" value="Quick suspension from user list">
                                    <button type="submit" class="text-orange-600 hover:text-orange-900 text-xs">
                                        7 Days
                                    </button>
                                </form>
                                @endif

                                <!-- Quick Strike -->
                                <form action="{{ route('admin.users.add-strike', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="reason" value="Quick strike from user list">
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-xs">
                                        + Strike
                                    </button>
                                </form>
                            </div>
                            @else
                            <form action="{{ route('admin.users.restore', $user) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900">
                                    Restore
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center">
                        <div class="text-gray-400">
                            <span class="text-4xl">👥</span>
                            <p class="mt-2 text-lg font-medium text-gray-900">No users found</p>
                            <p class="text-gray-500">No users match your current filters.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $users->links() }}
    </div>
    @endif
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
                <span class="text-blue-600 text-2xl">👥</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Users</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $users->total() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
                <span class="text-green-600 text-2xl">✅</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Active Users</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ $users->where('is_active', true)->whereNull('deleted_at')->where(function($q) {
                        $q->whereNull('suspended_until')->orWhere('suspended_until', '<', now());
                    })->count() }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
                <span class="text-red-600 text-2xl">⚠️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">High Risk Users</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ $users->whereIn('risk_level', ['high', 'critical'])->count() }}
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
                <p class="text-sm font-medium text-gray-500">Suspended</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ $users->where('suspended_until', '>', now())->count() }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection