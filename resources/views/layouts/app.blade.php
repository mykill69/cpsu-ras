<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F6F5F1">
    <title>@yield('title') · CPSU RAS</title>

    <link rel="shortcut icon" href="{{ asset('images/cpsu-logo.png') }}" type="image/png">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="h-full">
    <a href="#main"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:shadow">
        Skip to content
    </a>

    <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-full">
        @include('layouts.sidebar')

        <div class="lg:pl-64">
            @include('layouts.navigation')

            @if (session('maintenance_mode'))
                <div class="border-b border-amber-200 bg-amber-50">
                    <div class="mx-auto flex max-w-[1400px] items-start gap-2.5 px-4 py-2.5 sm:px-6 lg:px-8">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m0 3.75h.008M10.29 3.86 1.82 18a1.5 1.5 0 0 0 1.29 2.25h17.78A1.5 1.5 0 0 0 22.18 18L13.71 3.86a1.5 1.5 0 0 0-2.42 0Z" />
                        </svg>
                        <p class="text-sm text-amber-900">
                            <span class="font-semibold">Maintenance mode is on.</span>
                            Only administrators can sign in until it's turned off in Settings.
                        </p>
                    </div>
                </div>
            @endif

            <main id="main" class="mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
