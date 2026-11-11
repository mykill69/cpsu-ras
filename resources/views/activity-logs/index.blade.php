@extends('layouts.app')

@section('title', 'Activity Logs')

@push('styles')
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
    <div x-data="activityLogManager()" x-init="init()" class="space-y-6">
        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <div class="p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex-shrink-0 shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-primary-900">Activity Logs</h1>
                            <p class="text-sm text-primary-500 mt-1">Monitor and track all system activities</p>
                        </div>
                    </div>

                    @if(auth()->user()->canManageUsers())
                    <button @click="clearAllLogs()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 border border-red-200 rounded-xl text-sm font-semibold text-red-700 transition-all duration-200 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Clear All Logs
                    </button>
                    @endif
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 px-5 sm:px-6 pb-6">
                <div class="stat-card bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-primary-500 truncate">Total Activities</p>
                            <p class="text-2xl font-bold text-primary-900 mt-1">{{ $stats['total'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                            <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="stat-card bg-gradient-to-br from-gold-50 to-gold-100 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-primary-500 truncate">Today</p>
                            <p class="text-2xl font-bold text-primary-900 mt-1">{{ $stats['today'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                            <svg class="w-5 h-5 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="stat-card bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-primary-500 truncate">Last 7 Days</p>
                            <p class="text-2xl font-bold text-primary-900 mt-1">{{ $stats['week'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="stat-card bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-primary-500 truncate">Last 30 Days</p>
                            <p class="text-2xl font-bold text-primary-900 mt-1">{{ $stats['month'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="stat-card bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-primary-500 truncate">Active Users</p>
                            <p class="text-2xl font-bold text-primary-900 mt-1">{{ $stats['users'] }}</p>
                        </div>
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                            <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-primary-700 mb-1.5">Search</label>
                    <input type="text" x-model="search" @input.debounce.500="filterLogs" placeholder="Search activities..."
                        class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-primary-700 mb-1.5">Log Type</label>
                    <select x-model="logType" @change="filterLogs"
                        class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                        <option value="">All Types</option>
                        @foreach($logTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-primary-700 mb-1.5">Event</label>
                    <select x-model="event" @change="filterLogs"
                        class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                        <option value="">All Events</option>
                        @foreach($eventTypes as $eventType)
                            <option value="{{ $eventType }}">{{ ucfirst($eventType) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-primary-700 mb-1.5">User</label>
                    <select x-model="user" @change="filterLogs"
                        class="w-full rounded-xl border-primary-200 py-2.5 px-4 text-sm focus:border-gold-500 focus:ring-gold-500">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-primary-700 mb-1.5">Date Range</label>
                    <div class="flex items-center gap-1.5">
                        <input type="date" x-model="dateFrom" @change="filterLogs"
                            class="flex-1 min-w-0 rounded-lg border-primary-200 py-2 px-2 text-xs focus:border-gold-500 focus:ring-gold-500">
                        <span class="text-primary-400 text-xs flex-shrink-0">to</span>
                        <input type="date" x-model="dateTo" @change="filterLogs"
                            class="flex-1 min-w-0 rounded-lg border-primary-200 py-2 px-2 text-xs focus:border-gold-500 focus:ring-gold-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full w-full divide-y divide-primary-100">
                    <thead class="bg-primary-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-primary-600 uppercase">Activity</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-primary-600 uppercase">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-primary-600 uppercase">Event</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-primary-600 uppercase">Date & Time</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-primary-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-primary-50" id="activity-logs-body">
                        @include('activity-logs.partials.table')
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-primary-100" id="pagination-links">
                {{ $activities->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function activityLogManager() {
    return {
        search: '',
        logType: '',
        event: '',
        user: '',
        dateFrom: '',
        dateTo: '',

        init() {
            this.bindPaginationLinks();
        },

        filterLogs() {
            this.fetchLogs(1);
        },

        async fetchLogs(page = 1) {
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.logType) params.set('log_type', this.logType);
            if (this.event) params.set('event', this.event);
            if (this.user) params.set('user', this.user);
            if (this.dateFrom) params.set('date_from', this.dateFrom);
            if (this.dateTo) params.set('date_to', this.dateTo);
            params.set('page', page);

            // Show loading state
            document.querySelector('#activity-logs-body').style.opacity = '0.5';

            try {
                const response = await fetch('{{ route('activity-logs.index') }}?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    // Update table
                    if (data.table) {
                        document.querySelector('#activity-logs-body').innerHTML = data.table;
                    }
                    
                    // Update pagination
                    if (data.pagination) {
                        document.querySelector('#pagination-links').innerHTML = data.pagination;
                    }
                    
                    // Re-bind pagination links
                    this.bindPaginationLinks();
                } else {
                    console.error('Error:', data.message);
                }
            } catch (e) {
                console.error('Search failed:', e);
            } finally {
                // Restore opacity
                document.querySelector('#activity-logs-body').style.opacity = '1';
            }
        },

        bindPaginationLinks() {
            // Remove existing event listeners
            document.querySelectorAll('#pagination-links a').forEach(link => {
                link.removeEventListener('click', this.handlePaginationClick);
            });

            // Add new event listeners
            document.querySelectorAll('#pagination-links a').forEach(link => {
                link.addEventListener('click', this.handlePaginationClick.bind(this));
            });
        },

        handlePaginationClick(e) {
            e.preventDefault();
            
            const url = new URL(e.currentTarget.href);
            const page = url.searchParams.get('page') || 1;
            
            this.fetchLogs(page);
        },

        async viewDetails(activityId) {
            try {
                const response = await fetch(@json(parse_url(url('/activity-logs'), PHP_URL_PATH)) + '/' + activityId, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const activity = data.activity;
                    let html = '<div class="text-left space-y-3">';
                    html += '<p><strong>Description:</strong> ' + activity.description + '</p>';
                    html += '<p><strong>Event:</strong> ' + (activity.event || 'N/A') + '</p>';
                    html += '<p><strong>Log Name:</strong> ' + activity.log_name + '</p>';
                    html += '<p><strong>User:</strong> ' + (activity.causer ? activity.causer.name : 'System') + '</p>';
                    html += '<p><strong>Date:</strong> ' + new Date(activity.created_at).toLocaleString() + '</p>';
                    html += '</div>';

                    Swal.fire({
                        title: 'Activity Details',
                        html: html,
                        width: '600px',
                        confirmButtonColor: '#1a5632',
                        confirmButtonText: 'Close'
                    });
                }
            } catch (e) {
                console.error('Failed to load activity details:', e);
            }
        },

        async deleteLog(activityId) {
            const result = await Swal.fire({
                title: 'Delete Log?',
                text: 'Are you sure you want to delete this activity log?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#1a5632',
                confirmButtonText: 'Yes, delete!'
            });

            if (result.isConfirmed) {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                try {
                    const response = await fetch(@json(parse_url(url('/activity-logs'), PHP_URL_PATH)) + '/' + activityId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message,
                            confirmButtonColor: '#1a5632',
                            timer: 1500
                        });
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete log',
                        confirmButtonColor: '#1a5632'
                    });
                }
            }
        },

        async clearAllLogs() {
            const result = await Swal.fire({
                title: 'Clear All Logs?',
                text: 'This will delete all activity logs. This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#1a5632',
                confirmButtonText: 'Yes, clear all!'
            });

            if (result.isConfirmed) {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                try {
                    const response = await fetch(@json(parse_url(route('activity-logs.clear-all'), PHP_URL_PATH)), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        },
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cleared!',
                            text: data.message,
                            confirmButtonColor: '#1a5632',
                            timer: 1500
                        });
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to clear logs',
                        confirmButtonColor: '#1a5632'
                    });
                }
            }
        }
    }
}
</script>
@endpush