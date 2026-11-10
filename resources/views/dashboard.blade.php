@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div x-data="dashboardData()" x-init="init()">
        <!-- Page Header -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Overview of your records archive system
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button @click="refreshData()"
                    class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Total Folders -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Folders</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total_folders">0</p>
                    </div>
                </div>
            </div>

            <!-- Total Files -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Files</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total_files">0</p>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Users</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total_users">0</p>
                    </div>
                </div>
            </div>

            <!-- Storage Used -->
            <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-blue-100 rounded-xl">
                        <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-primary-500">Storage Used</p>
                        <p class="text-2xl font-bold text-primary-900" x-text="stats.storage_usage.formatted_used">0 B</p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-primary-400 mb-1">
                        <span x-text="stats.storage_usage.percentage + '%'">0%</span>
                        <span x-text="stats.storage_usage.formatted_total">0 GB</span>
                    </div>
                    <div class="w-full bg-primary-100 rounded-full h-2.5">
                        <div class="bg-gradient-to-r from-primary-600 to-gold-500 h-2.5 rounded-full transition-all duration-500"
                            :style="'width: ' + stats.storage_usage.percentage + '%'"></div>
                    </div>
                </div>
                <a href="{{ route('backup.index') }}"
                    class="mt-4 inline-flex items-center gap-1 text-xs text-gold-500 hover:text-gold-600 font-medium transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Manage Backup & Sync →
                </a>
            </div>
        </div>

        <!-- Recent Uploads & Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Uploads -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Uploads</h2>
                </div>
                <div class="p-6">
                    <template x-if="stats.recent_uploads.length === 0">
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No recent uploads</p>
                    </template>
                    <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="file in stats.recent_uploads" :key="file.id">
                            <li class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <span
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                            <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                            x-text="file.original_name"></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <span x-text="file.uploader?.name"></span> •
                                            <span x-text="file.formatted_size"></span> •
                                            <span x-text="new Date(file.created_at).toLocaleDateString()"></span>
                                        </p>
                                    </div>
                                    <div>
                                        <a :href="'/drive/download/' + file.id" @click.stop
                                            class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-2xl shadow-sm border border-primary-100">
                <div class="p-6 border-b border-primary-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-primary-900 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            Recent Activity
                        </h2>
                        <a href="{{ route('activity-logs.index') }}"
                            class="text-xs text-gold-500 hover:text-gold-600 font-medium transition-colors">
                            View All →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <template x-if="stats.recent_activities.length === 0">
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-primary-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-primary-400 text-sm">No recent activity</p>
                        </div>
                    </template>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <template x-for="(activity, index) in stats.recent_activities" :key="activity.id">
                                <li>
                                    <div class="relative pb-8">
                                        <!-- Timeline Line -->
                                        <span x-show="index !== stats.recent_activities.length - 1"
                                            class="absolute top-10 left-5 -ml-px h-full w-0.5 bg-primary-100"></span>

                                        <div class="relative flex gap-4">
                                            <!-- Avatar/Icon -->
                                            <div class="flex-shrink-0">
                                                <!-- Folder Created -->
                                                <span
                                                    x-show="activity.description?.includes('Folder') || activity.description?.includes('folder')"
                                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-gold-100 ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-gold-600" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path
                                                            d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                                                    </svg>
                                                </span>
                                                <!-- File Uploaded -->
                                                <span
                                                    x-show="activity.description?.includes('upload') || activity.description?.includes('File')"
                                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                    </svg>
                                                </span>
                                                <!-- Deleted -->
                                                <span x-show="activity.description?.includes('delet')"
                                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </span>
                                                <!-- Downloaded -->
                                                <span x-show="activity.description?.includes('download')"
                                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-green-600" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </span>
                                                <!-- User Activity -->
                                                <span
                                                    x-show="activity.description?.includes('User') || activity.description?.includes('user') || activity.description?.includes('account')"
                                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-purple-600" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </span>
                                                <!-- Default -->
                                                <span
                                                    x-show="!activity.description?.includes('Folder') && !activity.description?.includes('upload') && !activity.description?.includes('File') && !activity.description?.includes('delet') && !activity.description?.includes('download') && !activity.description?.includes('User') && !activity.description?.includes('user') && !activity.description?.includes('account')"
                                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </span>
                                            </div>

                                            <!-- Activity Content -->
                                            <div class="flex-1 min-w-0 pt-1.5">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <!-- User Name -->
                                                        <p class="text-sm font-medium text-primary-900 truncate"
                                                            x-text="activity.causer_name || 'System'"></p>
                                                        <!-- Activity Description -->
                                                        <p class="text-sm text-primary-500 mt-0.5"
                                                            x-text="activity.description"></p>
                                                    </div>
                                                    <!-- Time -->
                                                    <time class="flex-shrink-0 text-xs text-primary-400 whitespace-nowrap"
                                                        x-text="new Date(activity.created_at).toLocaleString()"></time>
                                                </div>

                                                <!-- Subject Badge -->
                                                <div class="mt-2" x-show="activity.subject_name">
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-primary-50 text-xs text-primary-600">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                        </svg>
                                                        <span x-text="activity.subject_name"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function dashboardData() {
                return {
                    stats: {
                        total_folders: {{ $stats['total_folders'] }},
                        total_files: {{ $stats['total_files'] }},
                        total_users: {{ $stats['total_users'] }},
                        storage_usage: @json($stats['storage_usage']),
                        recent_uploads: @json($stats['recent_uploads']),
                        recent_activities: @json($stats['recent_activities']),
                    },

                    async init() {
                        // Auto-refresh every 30 seconds
                        setInterval(() => this.refreshData(), 30000);
                    },

                    async refreshData() {
                        try {
                            const response = await fetch('/dashboard/stats');
                            const data = await response.json();
                            this.stats = data;
                        } catch (error) {
                            console.error('Failed to refresh dashboard data:', error);
                        }
                    }
                }
            }
        </script>
    @endpush

@endsection
