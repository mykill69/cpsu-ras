@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $canSeeLogs = auth()->user()->hasRole(['administrator', 'records_officer']);
    $maxDay = max(1, collect($uploadsByDay)->max('count'));
    $monthTotal = collect($uploadsByDay)->sum('count');
@endphp

@section('content')
    <div x-data="dashboardData()" x-init="init()" class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-[28px] leading-tight text-primary-900">{{ $greeting }}, {{ auth()->user()->first_name }}</h1>
                <p class="mt-1 text-sm text-gray-500">A quick read on what's moving through the archive.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="refreshData()" class="btn-ghost" :disabled="loading">
                    <svg class="h-4 w-4" :class="loading && 'animate-spin'" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span class="text-xs text-gray-500">Updated <span x-text="updatedLabel"></span></span>
                </button>
                <a href="{{ route('drive.index') }}" class="btn-primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    Upload records
                </a>
            </div>
        </div>

        <!-- Key figures: one ruled strip, not four floating cards -->
        <section class="panel grid grid-cols-2 divide-gray-100 lg:grid-cols-4 lg:divide-x" aria-label="Key figures">
            <div class="border-b border-r border-gray-100 p-5 lg:border-0">
                <p class="eyebrow">Folders</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 tabular"
                    x-text="fmt(stats.total_folders)">{{ number_format($stats['total_folders']) }}</p>
                <p class="mt-1 text-xs text-gray-500">Across all departments</p>
            </div>
            <div class="border-b border-gray-100 p-5 lg:border-0">
                <p class="eyebrow">Files</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 tabular"
                    x-text="fmt(stats.total_files)">{{ number_format($stats['total_files']) }}</p>
                <p class="mt-1 text-xs text-gray-500">
                    <span class="font-medium text-primary-700" x-text="'+' + fmt(stats.uploads_this_week)"></span>
                    this week
                </p>
            </div>
            <div class="border-r border-gray-100 p-5 lg:border-0">
                <p class="eyebrow">Active users</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 tabular"
                    x-text="fmt(stats.total_users)">{{ number_format($stats['total_users']) }}</p>
                <p class="mt-1 text-xs text-gray-500">With sign-in access</p>
            </div>
            <div class="p-5">
                <div class="flex items-baseline justify-between gap-2">
                    <p class="eyebrow">Storage</p>
                    <p class="text-xs text-gray-500 tabular" x-text="stats.storage_usage.percentage + '%'"></p>
                </div>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 tabular"
                    x-text="stats.storage_usage.formatted_used">{{ $stats['storage_usage']['formatted_used'] }}</p>
                <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-100" role="progressbar"
                    :aria-valuenow="stats.storage_usage.percentage" aria-valuemin="0" aria-valuemax="100">
                    <div class="h-full rounded-full transition-[width] duration-500"
                        :class="stats.storage_usage.percentage >= 85 ? 'bg-red-600' : (stats.storage_usage.percentage >= 70 ? 'bg-gold-500' : 'bg-primary-600')"
                        :style="'width:' + Math.max(stats.storage_usage.percentage, 0.5) + '%'"></div>
                </div>
                <p class="mt-1.5 text-xs text-gray-500">of <span x-text="stats.storage_usage.formatted_total"></span>
                    · <a href="{{ route('backup.index') }}" class="font-medium text-primary-700 hover:underline">Backup</a>
                </p>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">

                <!-- Upload volume, last 30 days -->
                <section class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Uploads, last 30 days</h2>
                        <p class="text-xs text-gray-500"><span class="font-medium text-gray-900 tabular">{{ number_format($monthTotal) }}</span> files</p>
                    </div>
                    <div class="px-5 pb-4 pt-5">
                        @if ($monthTotal === 0)
                            <p class="py-10 text-center text-sm text-gray-500">Nothing uploaded in the last 30 days.</p>
                        @else
                            <div class="flex h-36 items-end gap-[3px]" role="img"
                                aria-label="Daily uploads over the last 30 days, {{ $monthTotal }} in total">
                                @foreach ($uploadsByDay as $day)
                                    <div class="group relative flex h-full flex-1 items-end">
                                        <div class="w-full rounded-t-[2px] {{ $loop->last ? 'bg-gold-500' : 'bg-primary-600/80 group-hover:bg-primary-700' }}"
                                            style="height: {{ $day['count'] ? max(3, round($day['count'] / $maxDay * 100)) : 0 }}%"></div>
                                        @if (!$day['count'])
                                            <div class="absolute inset-x-0 bottom-0 h-px bg-gray-200"></div>
                                        @endif
                                        <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1.5 -translate-x-1/2 whitespace-nowrap rounded bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0 transition-opacity group-hover:opacity-100">
                                            {{ $day['label'] }} · {{ $day['count'] }} {{ Str::plural('file', $day['count']) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2 flex justify-between text-[11px] text-gray-400 tabular">
                                <span>{{ $uploadsByDay[0]['label'] }}</span>
                                <span>{{ $uploadsByDay[14]['label'] }}</span>
                                <span>Today</span>
                            </div>
                        @endif
                    </div>
                </section>

                <!-- Recent uploads -->
                <section class="panel overflow-hidden">
                    <div class="panel-header">
                        <h2 class="panel-title">Recent uploads</h2>
                        <a href="{{ route('drive.index') }}" class="text-xs font-medium text-primary-700 hover:underline">Open Drive</a>
                    </div>

                    <template x-if="stats.recent_uploads.length === 0">
                        <div class="px-5 py-12 text-center">
                            <p class="text-sm font-medium text-gray-900">No files yet</p>
                            <p class="mt-1 text-sm text-gray-500">Uploads will appear here as soon as someone adds a record.</p>
                        </div>
                    </template>

                    <div x-show="stats.recent_uploads.length" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-left text-xs text-gray-500">
                                    <th scope="col" class="px-5 py-2.5 font-medium">Name</th>
                                    <th scope="col" class="hidden px-3 py-2.5 font-medium md:table-cell">Uploaded by</th>
                                    <th scope="col" class="hidden px-3 py-2.5 text-right font-medium sm:table-cell">Size</th>
                                    <th scope="col" class="px-3 py-2.5 text-right font-medium">Date</th>
                                    <th scope="col" class="px-5 py-2.5"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="file in stats.recent_uploads" :key="file.id">
                                    <tr class="group hover:bg-gray-50/70">
                                        <td class="w-full max-w-0 px-5 py-2.5">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex h-6 w-11 shrink-0 items-center justify-center rounded font-mono text-[10px] font-medium uppercase"
                                                    :class="extClass(file.extension)" x-text="(file.extension || 'file').slice(0, 4)"></span>
                                                <span class="truncate font-medium text-gray-900" :title="file.original_name"
                                                    x-text="file.original_name"></span>
                                            </div>
                                        </td>
                                        <td class="hidden whitespace-nowrap px-3 py-2.5 text-gray-600 md:table-cell"
                                            x-text="file.uploader || '—'"></td>
                                        <td class="hidden whitespace-nowrap px-3 py-2.5 text-right text-gray-600 tabular sm:table-cell"
                                            x-text="file.formatted_size"></td>
                                        <td class="whitespace-nowrap px-3 py-2.5 text-right text-gray-600 tabular"
                                            x-text="shortDate(file.created_at)"></td>
                                        <td class="whitespace-nowrap px-5 py-2.5 text-right">
                                            <a :href="driveBaseUrl + '/download/' + file.id"
                                                class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-primary-700"
                                                :aria-label="'Download ' + file.original_name" title="Download">
                                                <svg class="inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- Activity feed -->
            <section class="panel flex flex-col xl:max-h-[calc(100vh-10rem)] xl:sticky xl:top-20">
                <div class="panel-header">
                    <h2 class="panel-title">Activity</h2>
                    @if ($canSeeLogs)
                        <a href="{{ route('activity-logs.index') }}" class="text-xs font-medium text-primary-700 hover:underline">Full log</a>
                    @endif
                </div>

                <template x-if="stats.recent_activities.length === 0">
                    <p class="px-5 py-12 text-center text-sm text-gray-500">No activity recorded yet.</p>
                </template>

                <ol class="flex-1 overflow-y-auto px-5 py-2">
                    <template x-for="(activity, i) in stats.recent_activities" :key="activity.id">
                        <li class="relative flex gap-3 py-3">
                            <span x-show="i !== stats.recent_activities.length - 1"
                                class="absolute left-[13px] top-10 bottom-0 w-px bg-gray-100" aria-hidden="true"></span>
                            <span class="relative flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-semibold"
                                :class="activity.causer_name === 'System' ? 'bg-gray-100 text-gray-500' : 'bg-primary-50 text-primary-800 ring-1 ring-primary-100'"
                                x-text="activity.causer_initials"></span>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <p class="text-sm leading-snug text-gray-600">
                                    <span class="font-medium text-gray-900" x-text="activity.causer_name"></span>
                                    <span x-text="lowerFirst(activity.description)"></span>
                                </p>
                                <p x-show="activity.subject_name" class="mt-1 truncate text-xs text-gray-500">
                                    <span class="text-gray-400" x-text="activity.subject_type"></span>
                                    <span class="text-gray-700" x-text="activity.subject_name"></span>
                                </p>
                                <time class="mt-1 block text-[11px] text-gray-400" :datetime="activity.created_at"
                                    :title="new Date(activity.created_at).toLocaleString()"
                                    x-text="timeAgo(activity.created_at)"></time>
                            </div>
                        </li>
                    </template>
                </ol>
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            function dashboardData() {
                return {
                    stats: @json($stats),
                    loading: false,
                    updatedAt: new Date(),
                    now: Date.now(),
                    driveBaseUrl: @json(parse_url(url('/drive'), PHP_URL_PATH)),
                    statsUrl: @json(parse_url(route('dashboard.stats'), PHP_URL_PATH)),

                    init() {
                        setInterval(() => this.refreshData(), 30000);
                        setInterval(() => this.now = Date.now(), 15000);
                    },

                    get updatedLabel() {
                        return this.updatedAt.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                    },

                    async refreshData() {
                        if (this.loading || document.hidden) return;
                        this.loading = true;
                        try {
                            const response = await fetch(this.statsUrl, { headers: { 'Accept': 'application/json' } });
                            if (!response.ok) throw new Error(response.status);
                            this.stats = await response.json();
                            this.updatedAt = new Date();
                        } catch (error) {
                            console.error('Failed to refresh dashboard data:', error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    fmt(n) {
                        return Number(n || 0).toLocaleString();
                    },

                    shortDate(value) {
                        const d = new Date(value);
                        const sameYear = d.getFullYear() === new Date().getFullYear();
                        return d.toLocaleDateString([], sameYear ? { month: 'short', day: 'numeric' } : { year: 'numeric', month: 'short', day: 'numeric' });
                    },

                    timeAgo(value) {
                        const seconds = Math.round((this.now - new Date(value).getTime()) / 1000);
                        if (seconds < 60) return 'just now';
                        const minutes = Math.round(seconds / 60);
                        if (minutes < 60) return minutes + ' min ago';
                        const hours = Math.round(minutes / 60);
                        if (hours < 24) return hours + (hours === 1 ? ' hour ago' : ' hours ago');
                        const days = Math.round(hours / 24);
                        if (days < 7) return days === 1 ? 'yesterday' : days + ' days ago';
                        return this.shortDate(value);
                    },

                    lowerFirst(text) {
                        if (!text) return '';
                        return text.charAt(0).toLowerCase() + text.slice(1);
                    },

                    extClass(ext) {
                        const groups = {
                            'bg-red-50 text-red-700': ['pdf'],
                            'bg-sky-50 text-sky-700': ['doc', 'docx', 'odt', 'rtf', 'txt'],
                            'bg-emerald-50 text-emerald-700': ['xls', 'xlsx', 'csv', 'ods'],
                            'bg-orange-50 text-orange-700': ['ppt', 'pptx', 'odp'],
                            'bg-violet-50 text-violet-700': ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tif', 'tiff'],
                        };
                        for (const [cls, list] of Object.entries(groups)) {
                            if (list.includes(ext)) return cls;
                        }
                        return 'bg-gray-100 text-gray-600';
                    },
                }
            }
        </script>
    @endpush
@endsection
