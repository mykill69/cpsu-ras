@if ($search !== '')
    <p class="border-b border-gray-100 bg-gray-50/60 px-4 py-2 text-sm text-gray-500 sm:px-6" role="status">
        <span class="tabular font-medium text-gray-800">{{ number_format($files->total()) }}</span>
        {{ Str::plural('result', $files->total()) }} for “{{ $search }}”
    </p>
@endif
