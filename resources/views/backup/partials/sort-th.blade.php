{{--
    Sortable column header.
    @include('backup.partials.sort-th', ['key' => 'name', 'label' => 'File name', 'firstDir' => 'asc'])
    Needs $sort and $direction from the page. The first click on a column uses
    $firstDir (A→Z for text, largest/newest for numbers and dates); clicking the
    active column flips it.
--}}
@php
    $active = $sort === $key;
    $next = $active ? ($direction === 'asc' ? 'desc' : 'asc') : ($firstDir ?? 'desc');
    // The controller saves this choice and redirects back to the clean URL.
    $href = request()->url() . '?' . http_build_query(['sort' => $key, 'direction' => $next]);
@endphp
<th scope="col" class="px-6 py-3 text-left"
    aria-sort="{{ $active ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    <a href="{{ $href }}"
        class="group inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-semibold uppercase tracking-wider {{ $active ? 'text-primary-800' : 'text-gray-500 hover:text-gray-900' }}">
        {{ $label }}
        <svg class="h-3.5 w-3.5 {{ $active ? 'text-primary-700' : 'text-gray-300 group-hover:text-gray-500' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            @if ($active && $direction === 'asc')
                <path fill-rule="evenodd" d="M10 5a1 1 0 0 1 .7.3l4 4a1 1 0 1 1-1.4 1.4L10 7.4l-3.3 3.3a1 1 0 0 1-1.4-1.4l4-4A1 1 0 0 1 10 5Z" clip-rule="evenodd" />
            @elseif ($active)
                <path fill-rule="evenodd" d="M10 15a1 1 0 0 1-.7-.3l-4-4a1 1 0 1 1 1.4-1.4l3.3 3.3 3.3-3.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-.7.3Z" clip-rule="evenodd" />
            @else
                <path d="M10 3.5 13.5 7h-7L10 3.5ZM10 16.5 6.5 13h7L10 16.5Z" />
            @endif
        </svg>
    </a>
</th>
