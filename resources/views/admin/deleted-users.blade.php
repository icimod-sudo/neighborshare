@extends('layouts.admin')

@section('title', 'Deleted Users - Admin Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Deleted Users Management</h1>
    <p class="text-gray-600">Manage permanently deleted user accounts</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
                <span class="text-red-600 text-2xl">🚫</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Deleted</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $users->total() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
        <div class="flex items-center">
            <div class="p-3 bg-orange-100 rounded-lg">
                <span class="text-orange-600 text-2xl">🕵️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Fraud Cases</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ $users->where('deleted_reason', 'like', '%fraud%')->count() }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
                <span class="text-blue-600 text-2xl">👤</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Deleted Today</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ $users->where('deleted_at', '>=', today())->count() }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
                <span class="text-green-600 text-2xl">↩️</span>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Can Be Restored</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $users->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div class="flex-1 max-w-md">
            <form method="GET" action="{{ route('admin.deleted-users') }}">
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search deleted users..."
                        value="{{ request('search') }}"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                        <span class="text-gray-400">🔍</span>
                    </div>
                </div>
            </form>
        </div>

        <div class="flex space-x-3">
            <a href="{{ route('admin.users') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                <span class="mr-2">←</span>
                Back to Users
            </a>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Deleted User Accounts</h3>
    </div>

    @if($users->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stats</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-red-600 font-semibold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                @if($user->phone)
                                <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $user->deleted_at->format('M j, Y') }}</div>
                        <div class="text-sm text-gray-500">{{ $user->deleted_at->format('g:i A') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->deleted_by)
                        @if($user->deleted_by === $user->id)
                        <!-- User deleted themselves -->
                        <div class="inline-flex flex-col items-start">
                            <div class="flex items-center space-x-2">
                                <span class="text-purple-500">👤</span>
                                <span class="text-sm font-medium text-gray-900">Self</span>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mt-1">
                                Voluntary
                            </span>
                        </div>
                        @else
                        <!-- Deleted by admin -->
                        <div class="inline-flex flex-col items-start">
                            <div class="flex items-center space-x-2">
                                <span class="text-red-500">🛡️</span>
                                <span class="text-sm font-medium text-gray-900">
                                    @if($user->deleter)
                                    {{ $user->deleter->name }}
                                    @else
                                    Admin #{{ $user->deleted_by }}
                                    @endif
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                Admin Action
                            </span>
                        </div>
                        @endif
                        @else
                        <!-- No deleter recorded -->
                        <div class="inline-flex flex-col items-start">
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500">⚙️</span>
                                <span class="text-sm font-medium text-gray-900">System</span>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                                Automated
                            </span>
                        </div>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        @if($user->deleted_reason)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if(str_contains(strtolower($user->deleted_reason), 'fraud')) bg-red-100 text-red-800
                                    @elseif(str_contains(strtolower($user->deleted_reason), 'spam')) bg-orange-100 text-orange-800
                                    @elseif(str_contains(strtolower($user->deleted_reason), 'violation')) bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                            {{ Str::limit($user->deleted_reason, 30) }}
                        </span>
                        @else
                        <span class="text-sm text-gray-500">No reason provided</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                                🎁 {{ $user->products_count }}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800">
                                🔄 {{ $user->sent_exchanges_count + $user->received_exchanges_count }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.users.detail', $user->id) }}"
                                class="text-blue-600 hover:text-blue-900 flex items-center">
                                <span class="mr-1">👁️</span> View
                            </a>

                            <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="inline">
                                @csrf
                                @method('POST')
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to restore this user?')"
                                    class="text-green-600 hover:text-green-900 flex items-center">
                                    <span class="mr-1">↩️</span> Restore
                                </button>
                            </form>

                            <form action="{{ route('admin.users.force-delete', $user->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('⚠️ WARNING: This will PERMANENTLY delete this user and all associated data. This action cannot be undone! Are you absolutely sure?')"
                                    class="text-red-600 hover:text-red-900 flex items-center">
                                    <span class="mr-1">🗑️</span> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $users->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <div class="text-6xl mb-4">🎉</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No deleted users found</h3>
        <p class="text-gray-500 mb-4">There are no permanently deleted user accounts in the system.</p>
        <a href="{{ route('admin.users') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
            ← Back to User Management
        </a>
    </div>
    @endif
</div>

<!-- Quick Actions -->
<div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    <a href="{{ route('admin.users') }}?status=banned"
        class="p-4 bg-white rounded-lg shadow border border-gray-200 text-center hover:bg-gray-50 transition-colors">
        <span class="text-2xl block mb-2">🚫</span>
        <p class="font-medium text-gray-900">Banned Users</p>
        <p class="text-sm text-gray-500">View active bans</p>
    </a>

    <a href="{{ route('admin.high-risk-users') }}"
        class="p-4 bg-white rounded-lg shadow border border-gray-200 text-center hover:bg-gray-50 transition-colors">
        <span class="text-2xl block mb-2">⚠️</span>
        <p class="font-medium text-gray-900">High Risk Users</p>
        <p class="text-sm text-gray-500">Monitor risky accounts</p>
    </a>

    <a href="{{ route('admin.fraud-reports') }}"
        class="p-4 bg-white rounded-lg shadow border border-gray-200 text-center hover:bg-gray-50 transition-colors">
        <span class="text-2xl block mb-2">📊</span>
        <p class="font-medium text-gray-900">Fraud Reports</p>
        <p class="text-sm text-gray-500">View analytics</p>
    </a>
</div>
@endsection