@extends('layouts.app')

@section('title', 'Backup & Sync')

@section('content')
    <div x-data="backupManager()">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg flex-shrink-0">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    class="inline-flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-xl text-sm font-semibold text-white shadow-md hover:shadow-lg transition-all duration-200 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync All Files
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl p-5 border border-primary-200">
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
                    class="bg-gradient-to-br from-gold-50 to-gold-100 rounded-xl p-5 border border-gold-200 hover:shadow-md transition-all duration-200 cursor-pointer block">
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
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-200 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-700">{{ \App\Models\File::formatBytes($totalSize) }}</p>
                            <p class="text-xs text-blue-600">Total Size</p>
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
                    <span class="text-xs text-primary-400 font-normal">({{ $files->total() }} files)</span>
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden w-full">
                <div class="overflow-x-auto w-full">
                    <table class="w-full divide-y divide-primary-100">
                        <thead>
                            <tr class="bg-primary-50/30">
                                <th
                                    class="px-6 py-3.5 text-left text-xs font-semibold text-primary-600 uppercase tracking-wider">
                                    File Name</th>
                                <th
                                    class="px-6 py-3.5 text-left text-xs font-semibold text-primary-600 uppercase tracking-wider">
                                    Size</th>
                                <th
                                    class="px-6 py-3.5 text-left text-xs font-semibold text-primary-600 uppercase tracking-wider">
                                    Type</th>
                                <th
                                    class="px-6 py-3.5 text-left text-xs font-semibold text-primary-600 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3.5 text-left text-xs font-semibold text-primary-600 uppercase tracking-wider">
                                    Date Uploaded</th>
                                <th
                                    class="px-6 py-3.5 text-center text-xs font-semibold text-primary-600 uppercase tracking-wider">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary-50">
                            @forelse($files as $file)
                                <tr class="hover:bg-primary-50/30 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                                    @if ($file->mime_type === 'application/pdf') bg-red-100
                                    @elseif(str_starts_with($file->mime_type, 'image/')) bg-purple-100
                                    @elseif(in_array($file->extension, ['doc', 'docx'])) bg-blue-100
                                    @elseif(in_array($file->extension, ['xls', 'xlsx'])) bg-green-100
                                    @else bg-primary-100 @endif">
                                                @if ($file->mime_type === 'application/pdf')
                                                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                @elseif(str_starts_with($file->mime_type, 'image/'))
                                                    <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-primary-600" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-primary-900 truncate max-w-[250px]"
                                                    title="{{ $file->original_name }}">
                                                    {{ $file->original_name }}
                                                </p>
                                                <p class="text-xs text-primary-400 mt-0.5">
                                                    {{ $file->uploader->name ?? 'Unknown' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-primary-600">{{ $file->formatted_size }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2.5 py-1 rounded-lg bg-primary-100 text-xs font-semibold text-primary-700 uppercase">
                                            {{ $file->extension }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-sm text-primary-500">{{ $file->created_at->format('M d, Y') }}</span>
                                        <p class="text-xs text-primary-400 mt-0.5">
                                            {{ $file->created_at->format('h:i A') }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button type="button" onclick="syncFile({{ $file->id }})"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-lg text-xs font-semibold text-white shadow-sm hover:shadow-md transition-all duration-200">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            Sync Now
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div
                                            class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-primary-900 mb-1">All files are backed up!
                                        </h3>
                                        <p class="text-sm text-primary-500">No pending files to sync. All files are safely
                                            backed up.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($files->hasPages())
                <div class="px-6 py-4 border-t border-primary-100 bg-primary-50/30">
                    {{ $files->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
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
                                const response = await fetch('{{ route('backup.sync-all') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': csrf,
                                        'Accept': 'application/json'
                                    }
                                });
                                const data = await response.json();
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
                                    text: 'Sync failed. Please try again.',
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
                    const response = await fetch(`/backup/sync/${fileId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });
                    const data = await response.json();
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
