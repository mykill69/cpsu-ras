@extends('layouts.app')

@section('title', 'Backup & Sync')

@section('content')
    <div x-data="backupManager()">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-primary-50 text-primary-700 ring-1 ring-primary-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-primary-900">Backup & Sync</h1>
                        <p class="text-sm text-primary-500 mt-0.5">Sync files to backup storage for safekeeping</p>
                    </div>
                </div>
                <button @click="syncAll()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-700 hover:bg-primary-800 rounded-lg text-sm font-medium text-white transition-all duration-200 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync All Files
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                <div class="bg-primary-50 rounded-xl p-5 border border-primary-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-200 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-primary-900">{{ $totalFiles }}</p>
                            <p class="text-xs text-primary-500">Total Files</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('backup.synced') }}"
                    class="bg-gold-50 rounded-xl p-5 border border-gold-200 hover:shadow-md transition-all duration-200 cursor-pointer block">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gold-200 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gold-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gold-700">{{ $syncedCount }}</p>
                            <p class="text-xs text-gold-600">Synced Files</p>
                        </div>
                    </div>
                </a>
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-200 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800">{{ \App\Models\File::formatBytes($totalSize) }}</p>
                            <p class="text-xs text-gray-500">Total Size</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Files Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-primary-100 bg-primary-50/50">
                <h2 class="text-lg font-semibold text-primary-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Pending Backup Files
                    <span class="text-xs text-primary-400 font-normal">({{ $pendingTotal }} {{ Str::plural('file', $pendingTotal) }})</span>
                </h2>
            </div>

            <div x-data="fileTable('{{ route('backup.index') }}', @js($search))">
                @include('backup.partials.search-bar')

                <div x-ref="table" @click="navigate($event)" class="transition-opacity" :class="loading && 'opacity-50'" :aria-busy="loading.toString()">
                    @include('backup.partials.pending-table')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Safely parse a JSON response; gives a readable error when the server
            // returns an HTML page (404/419/500) instead of JSON.
            async function readBackupJson(response) {
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Server returned HTTP ' + response.status + '. Please refresh the page and try again.');
                }
            }

            function backupManager() {
                return {
                    async syncAll() {
                        const result = await Swal.fire({
                            title: 'Sync All Files?',
                            text: 'This will backup all pending files to backup storage.',
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonColor: '#1a5632',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Yes, sync all!'
                        });

                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Syncing files...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                            try {
                                const response = await fetch(@json(parse_url(route('backup.sync-all'), PHP_URL_PATH)), {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': csrf,
                                        'Accept': 'application/json'
                                    }
                                });
                                const data = await readBackupJson(response);
                                if (!data.success) {
                                    throw new Error(data.message || 'Sync failed.');
                                }
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Done!',
                                    text: data.message,
                                    confirmButtonColor: '#1a5632'
                                });
                                setTimeout(() => window.location.reload(), 1500);
                            } catch (e) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Sync failed: ' + e.message,
                                    confirmButtonColor: '#1a5632'
                                });
                            }
                        }
                    }
                }
            }

            async function syncFile(fileId) {
                Swal.fire({
                    title: 'Syncing file...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                try {
                    const response = await fetch(@json(parse_url(url('/backup'), PHP_URL_PATH)) + '/sync/' + fileId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await readBackupJson(response);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Synced!',
                            text: data.message,
                            confirmButtonColor: '#1a5632',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        // Force reload after Swal closes
                        setTimeout(() => {
                            window.location.reload(true);
                        }, 1500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: data.message,
                            confirmButtonColor: '#1a5632'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Sync failed: ' + e.message,
                        confirmButtonColor: '#1a5632'
                    });
                }
            }
        </script>
    @endpush
@endsection
