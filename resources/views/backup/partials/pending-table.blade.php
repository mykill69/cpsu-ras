{{-- Pending files table. Rendered with the page, and on its own when the
     search/sort/pagination is fetched in the background. --}}
@include('backup.partials.result-count')

<div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden w-full">
    <div class="overflow-x-auto w-full">
        <table class="w-full divide-y divide-primary-100">
            <thead>
                <tr class="bg-primary-50/30">
                    @include('backup.partials.sort-th', ['key' => 'name', 'label' => 'File name', 'firstDir' => 'asc'])
                    @include('backup.partials.sort-th', ['key' => 'size', 'label' => 'Size', 'firstDir' => 'desc'])
                    @include('backup.partials.sort-th', ['key' => 'type', 'label' => 'Type', 'firstDir' => 'asc'])
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                    @include('backup.partials.sort-th', ['key' => 'uploaded', 'label' => 'Date uploaded', 'firstDir' => 'desc'])
                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Action</th>
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
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg text-xs font-medium text-gray-800 transition-all duration-200">
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
                            @if ($search !== '')
                            <h3 class="text-base font-semibold text-gray-900">No pending files match “{{ $search }}”</h3>
                            <p class="mt-1 text-sm text-gray-500">Try part of the file name, a type like “pdf”, or the uploader’s name.</p>
                            <a href="{{ request()->url() }}?q=" data-clear-search class="btn-secondary mt-4 text-sm">Clear search</a>
                            @else
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
                            @endif
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
