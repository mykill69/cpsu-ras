@extends('layouts.app')

@section('title', 'Preview - ' . $file->original_name)

@push('styles')
<style>
    .pdf-container {
        width: 100%;
        height: 80vh;
    }
    .pdf-container iframe,
    .pdf-container embed {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 1rem;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-5 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                    @if($isPdf)
                    <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    @else
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl font-bold text-primary-900 truncate">{{ $file->original_name }}</h1>
                    <p class="text-sm text-primary-500">{{ $file->formatted_size }} · {{ strtoupper($file->extension) }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('drive.files.download', $file->id) }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-700 hover:bg-primary-800 rounded-lg text-sm font-medium text-white transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download
                </a>
                
                <a href="javascript:history.back()" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border-2 border-primary-200 hover:bg-primary-50 rounded-xl text-sm font-semibold text-primary-700 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Preview Content -->
    <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
        @if($isPdf)
            <!-- PDF Preview using browser's built-in viewer -->
            <div class="pdf-container">
                <embed src="{{ route('drive.files.stream', $file->id) }}#toolbar=0&navpanes=0&scrollbar=0" 
                       type="application/pdf"
                       title="{{ $file->original_name }}">
            </div>
        @elseif($isImage)
            <!-- Image Preview -->
            <div class="flex items-center justify-center p-8 bg-gray-50" style="min-height: 70vh;">
                <img src="{{ route('drive.files.stream', $file->id) }}" 
                     alt="{{ $file->original_name }}" 
                     class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-lg">
            </div>
        @else
            <div class="flex flex-col items-center justify-center p-20">
                <svg class="w-24 h-24 text-primary-300 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-lg text-primary-600 mb-4">Preview not available</p>
                <a href="{{ route('drive.files.download', $file->id) }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary-700 hover:bg-primary-800 rounded-lg text-sm font-medium text-white">
                    Download File
                </a>
            </div>
        @endif
    </div>
</div>
@endsection