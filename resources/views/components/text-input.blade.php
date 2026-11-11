@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'rounded-lg border-gray-300 bg-white px-3.5 py-2.5 text-[15px] text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary-600 focus:ring-primary-600 disabled:bg-gray-50 disabled:text-gray-500']) !!}>
