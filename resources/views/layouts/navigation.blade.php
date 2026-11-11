<header class="sticky top-0 z-30 border-b border-gray-200/80 bg-paper/90 backdrop-blur supports-[backdrop-filter]:bg-paper/75">
    <div class="mx-auto flex h-14 max-w-[1400px] items-center gap-3 px-4 sm:px-6 lg:px-8">
        <button type="button" @click="sidebarOpen = true"
            class="-ml-2 rounded-md p-2 text-gray-600 hover:bg-gray-200/60 hover:text-gray-900 lg:hidden">
            <span class="sr-only">Open menu</span>
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 lg:hidden">
            <img src="{{ asset('images/cpsu-logo.png') }}" alt="" class="h-7 w-7" width="28" height="28">
            <span class="font-serif text-[15px] font-semibold text-primary-900">CPSU Records</span>
        </a>

        <p class="hidden min-w-0 truncate text-sm text-gray-500 lg:block">
            <span>Records Archive</span>
            <span class="mx-1.5 text-gray-300">/</span>
            <span class="font-medium text-gray-900">@yield('title')</span>
        </p>

        <div class="ml-auto flex items-center gap-3">
            <time datetime="{{ now()->toDateString() }}" class="hidden text-xs text-gray-500 sm:block tabular">
                {{ now()->format('l, F j, Y') }}
            </time>
        </div>
    </div>
</header>
