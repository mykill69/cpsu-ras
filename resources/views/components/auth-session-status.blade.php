@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-800']) }} role="status">
        {{ $status }}
    </div>
@endif
