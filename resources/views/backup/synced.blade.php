@extends('layouts.app')

@section('title', 'Synced Files')


@section('content')
<div class="space-y-6">
    <div class="panel flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 text-primary-700 ring-1 ring-primary-100">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-semibold text-primary-900">Synced Files</h1>
                <p class="text-sm text-gray-500">
                    <span class="tabular">{{ number_format($total) }}</span> {{ Str::plural('file', $total) }} backed up to Google Drive
                </p>
            </div>
        </div>
        <a href="{{ route('backup.index') }}" class="btn-ghost text-sm">← Back to Backup</a>
    </div>

    <div class="panel overflow-hidden" x-data="fileTable('{{ route('backup.synced') }}', @js($search))">
        @include('backup.partials.search-bar')

        <div x-ref="table" @click="navigate($event)" class="transition-opacity" :class="loading && 'opacity-50'" :aria-busy="loading.toString()">
            @include('backup.partials.synced-table')
        </div>
    </div>
</div>
@endsection
