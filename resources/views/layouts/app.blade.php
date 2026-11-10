<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CPSU RAS - @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />
    <link rel="shortcut icon" href="{{ asset('images/cpsu-logo.png') }}" type="image/x-icon">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="h-full bg-gradient-to-br from-primary-50 via-white to-gold-50">
    <div x-data="{ sidebarOpen: false }" class="min-h-full">
        <!-- Navigation -->
        @include('layouts.navigation')

        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <main class="lg:pl-72">
            <!-- Maintenance Mode Banner - Inside main content -->
            @if(session('maintenance_mode'))
                <div class="bg-yellow-50 border-b border-yellow-200">
                    <div class="px-4 sm:px-6 lg:px-8 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-sm text-yellow-800 font-medium text-center">
                                <strong>Maintenance Mode Active:</strong> System is currently in maintenance mode. Only administrators can access the system.
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="py-4 px-4 sm:px-6 lg:px-8 w-full">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>

</html>