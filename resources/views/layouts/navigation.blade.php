<nav x-data="{ 
    open: false, 
    pendingExchangesCount: {{ $pendingExchangesCount ?? 0 }},
    isLoading: false,
    lastUpdate: null
}"
    class="bg-white border-b border-gray-100"
    x-init="
        let previousCount = {{ $pendingExchangesCount ?? 0 }};
        
        // Function to update exchange count
        const updateExchangeCount = async () => {
            if (isLoading) return;
            
            isLoading = true;
            try {
                const response = await fetch('{{ route('exchanges.count') }}');
                const data = await response.json();
                if (data.success) {
                    const currentCount = data.count;
                    
                    // Check if new requests arrived
                    if (currentCount > previousCount && previousCount > 0) {
                        const newRequests = currentCount - previousCount;
                        showNewRequestNotification(newRequests);
                    }
                    
                    pendingExchangesCount = currentCount;
                    previousCount = currentCount;
                    lastUpdate = new Date();
                }
            } catch (error) {
                console.error('Failed to fetch exchange count:', error);
            } finally {
                isLoading = false;
            }
        };
        
        // Show notification for new requests
        const showNewRequestNotification = (newCount) => {
            // Browser notification
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('New Exchange Request!', {
                    body: `You have ${newCount} new exchange request${newCount > 1 ? 's' : ''}`,
                    icon: '/favicon.ico',
                    tag: 'exchange-request'
                });
            }
            
            // Show a toast notification
            showToastNotification(newCount);
        };
        
        // Toast notification function
        const showToastNotification = (count) => {
            // Create a toast element
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-bounce';
            toast.innerHTML = `
                <div class='flex items-center'>
                    <span class='mr-2'>🎁</span>
                    <span>You have ${count} new exchange request${count > 1 ? 's' : ''}!</span>
                    <button onclick='this.parentElement.parentElement.remove()' class='ml-4 text-white font-bold'>×</button>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        };
        
        // Request notification permission on page load
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
        
        // Initial load
        updateExchangeCount();
        
        // Poll every 30 seconds for real-time updates
        const pollInterval = setInterval(updateExchangeCount, 30000);
        
        // Also update when page becomes visible
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                updateExchangeCount();
            }
        });
        
        // Cleanup on navigation
        return () => {
            clearInterval(pollInterval);
        }
     ">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 hidden sm:block">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 sm:space-x-3 group">
                        <!-- Styled Plant -->
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-all duration-300">
                            <span class="text-white text-sm">🌿</span>
                        </div>

                        <!-- Text -->
                        <span class="font-bold text-gray-900 text-lg sm:text-xl">Gwache App</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>

                @if(Auth::check() && Auth::user()->is_admin)
                <div class="hidden sm:flex sm:items-center space-x-8">
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->is('admin*')">
                        {{ __('Admin Panel') }}
                    </x-nav-link>
                </div>
                @endif
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Desktop Navigation Links -->
        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
            <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                {{ __('Browse Products') }}
            </x-nav-link>
            <x-nav-link :href="route('products.map')" :active="request()->routeIs('products.map')">
                {{ __('Map View') }}
            </x-nav-link>
            <x-nav-link :href="route('products.my')" :active="request()->routeIs('products.my')">
                {{ __('My Products') }}
            </x-nav-link>

            <!-- My Exchanges - Badge ALWAYS shows regardless of current page -->
            <div class="relative">
                <x-nav-link :href="route('exchanges.my')" :active="request()->routeIs('exchanges.my')" class="flex items-center">
                    {{ __('My Exchanges') }}
                    <!-- Show badge ALWAYS (no conditions) -->
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs min-w-5 h-5 flex items-center justify-center animate-pulse"
                        x-text="pendingExchangesCount"
                        x-show="pendingExchangesCount > 0"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform scale-75"
                        x-transition:enter-end="opacity-100 transform scale-100"></span>
                    <!-- Show loading indicator -->
                    <template x-if="isLoading">
                        <svg class="ml-1 w-3 h-3 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                </x-nav-link>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')">
                    {{ __('Browse Products') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('products.map')" :active="request()->routeIs('products.map')">
                    {{ __('Map View') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('products.my')" :active="request()->routeIs('products.my')">
                    {{ __('My Products') }}
                </x-responsive-nav-link>

                <!-- My Exchanges with ALWAYS visible badge on mobile -->
                <div class="relative">
                    <x-responsive-nav-link :href="route('exchanges.my')" :active="request()->routeIs('exchanges.my')" class="flex items-center justify-between">
                        <span>{{ __('My Exchanges') }}</span>
                        <div class="flex items-center">
                            <!-- Show badge ALWAYS (no conditions) -->
                            <span class="ml-2 bg-red-500 text-white rounded-full text-xs min-w-5 h-5 flex items-center justify-center animate-pulse"
                                x-text="pendingExchangesCount"
                                x-show="pendingExchangesCount > 0"></span>
                            <!-- Show loading indicator -->
                            <template x-if="isLoading">
                                <svg class="ml-2 w-3 h-3 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                        </div>
                    </x-responsive-nav-link>
                </div>
            </div>

            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>