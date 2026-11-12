{{--
    Search box for a file table. Lives inside an x-data="fileTable(...)" block:
    typing reloads only the table below, in the background.
--}}
<div class="flex flex-wrap items-center gap-3 border-b border-gray-200 px-4 py-3 sm:px-6">
    <form class="relative w-full sm:w-80" role="search" @submit.prevent="search()">
        <label for="file-search" class="sr-only">Search files</label>
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
        </svg>
        <input id="file-search" type="search" name="q" x-model="q" autocomplete="off"
            placeholder="Search by file name, type or uploader"
            @input.debounce.300ms="search()" @keydown.escape="clear()"
            class="block w-full rounded-lg border-gray-300 bg-white py-2 pl-9 pr-9 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary-600 focus:ring-primary-600">
        <svg x-show="loading" x-cloak class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-primary-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3" />
            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
        </svg>
    </form>

    <button type="button" x-show="q !== ''" x-cloak @click="clear()" class="btn-secondary py-2 text-sm">Clear</button>
</div>
