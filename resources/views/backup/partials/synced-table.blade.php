{{-- Synced files table. Rendered with the page, and on its own when the
     search/sort/pagination is fetched in the background. --}}
@include('backup.partials.result-count')

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50/70">
            <tr>
                @include('backup.partials.sort-th', ['key' => 'name', 'label' => 'File name', 'firstDir' => 'asc'])
                @include('backup.partials.sort-th', ['key' => 'size', 'label' => 'Size', 'firstDir' => 'desc'])
                @include('backup.partials.sort-th', ['key' => 'type', 'label' => 'Type', 'firstDir' => 'asc'])
                @include('backup.partials.sort-th', ['key' => 'synced', 'label' => 'Synced at', 'firstDir' => 'desc'])
                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Drive link</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($files as $file)
                <tr class="transition-colors duration-150 hover:bg-gray-50">
                    <td class="w-full max-w-0 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $file->uploader->name ?? 'Unknown' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="tabular whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $file->formatted_size }}</td>
                    <td class="whitespace-nowrap px-6 py-4">
                        <span class="inline-flex rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold uppercase text-gray-700">{{ $file->extension }}</span>
                    </td>
                    <td class="tabular whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                        {{ $file->updated_at->format('M d, Y h:i A') }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-center">
                        @php
                            $metadata = is_string($file->metadata) ? json_decode($file->metadata, true) : ($file->metadata ?? []);
                            $driveLink = $metadata['google_drive_link'] ?? null;
                        @endphp
                        @if ($driveLink)
                            <a href="{{ $driveLink }}" target="_blank" rel="noopener noreferrer" class="btn-secondary px-3 py-1.5 text-xs">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                View
                            </a>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        @if ($search !== '')
                            <h3 class="text-base font-semibold text-gray-900">No synced files match “{{ $search }}”</h3>
                            <p class="mt-1 text-sm text-gray-500">Try part of the file name, a type like “pdf”, or the uploader’s name.</p>
                            <a href="{{ request()->url() }}?q=" data-clear-search class="btn-secondary mt-4 text-sm">Clear search</a>
                        @else
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900">No synced files</h3>
                            <p class="mt-1 text-sm text-gray-500">Files that are synced to Google Drive will appear here.</p>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($files->hasPages())
    <div class="border-t border-gray-200 px-6 py-4">
        {{ $files->links() }}
    </div>
@endif
