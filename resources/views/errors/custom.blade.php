@extends('layouts.app')

@section('title', 'Error')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-8">
    <div class="text-center">
        <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h1 class="text-2xl font-bold text-primary-900 mb-2">Something went wrong</h1>
        <p class="text-primary-600 mb-4">{{ $message }}</p>
        <a href="{{ route('dashboard') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gold-500 hover:bg-gold-600 rounded-xl text-sm font-semibold text-primary-900">
            Go to Dashboard
        </a>
    </div>
</div>
@endsection