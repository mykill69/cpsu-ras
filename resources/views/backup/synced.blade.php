@extends('layouts.app')

@section('title', 'Synced Files')

@section('content')
<div>
    <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-5 sm:p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-primary-900">Synced Files</h1>
                    <p class="text-sm text-primary-500">Files backed up to Google Drive</p>
                </div>
            </div>
            <a href="{{ route('backup.index') }}" class="text-sm text-gold-500 hover:text-gold-600 font-medium">
                ← Back to Backup
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-primary-100">
                <thead>
                    <tr class="bg-primary-50/30">
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-primary-600 uppercase tracking-wider">File Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-primary-600 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-primary-600 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-primary-600 uppercase tracking-wider">Synced At</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-primary-600 uppercase tracking-wider">Drive Link</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($files as $file)
                    <tr class="hover:bg-primary-50/30 transition-colors duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-primary-900 truncate max-w-[250px]">{{ $file->original_name }}</p>
                                    <p class="text-xs text-primary-400">{{ $file->uploader->name ?? 'Unknown' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-600">{{ $file->formatted_size }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2.5 py-1 rounded-lg bg-primary-100 text-xs font-semibold text-primary-700 uppercase">{{ $file->extension }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-500">
                            {{ $file->updated_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $metadata = is_string($file->metadata) ? json_decode($file->metadata, true) : ($file->metadata ?? []);
                                $driveLink = $metadata['google_drive_link'] ?? null;
                            @endphp
                            @if($driveLink)
                            <a href="{{ $driveLink }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-100 hover:bg-blue-200 rounded-lg text-xs font-semibold text-blue-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                View
                            </a>
                            @else
                            <span class="text-xs text-primary-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-primary-900 mb-1">No synced files</h3>
                            <p class="text-sm text-primary-500">Files that are synced to Google Drive will appear here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($files->hasPages())
        <div class="px-6 py-4 border-t border-primary-100">
            {{ $files->links() }}
        </div>
        @endif
    </div>
</div>
@endsection