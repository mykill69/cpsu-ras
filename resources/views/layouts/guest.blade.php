<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'CPSU RAS') }}</title>
        <link rel="shortcut icon" href="{{ asset('images/cpsu-logo.png') }}" type="image/png">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full">
        <div class="flex min-h-full flex-col items-center justify-center px-5 py-12">
            <a href="{{ url('/') }}" class="mb-8 flex items-center gap-3">
                <x-application-logo class="h-11 w-11" />
                <span class="leading-tight">
                    <span class="block font-serif text-lg font-semibold text-primary-900">CPSU Records</span>
                    <span class="block text-xs text-gray-500">Archive System</span>
                </span>
            </a>

            <div class="panel w-full max-w-[420px] px-6 py-7 sm:px-8">
                {{ $slot }}
            </div>

            <p class="mt-8 text-xs text-gray-400">&copy; {{ date('Y') }} Central Philippines State University</p>
        </div>
    </body>
</html>
