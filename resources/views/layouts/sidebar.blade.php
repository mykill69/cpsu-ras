<!-- Mobile drawer -->
<div x-show="sidebarOpen" x-cloak class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
    <div x-show="sidebarOpen" x-transition.opacity.duration.200ms @click="sidebarOpen = false"
        class="fixed inset-0 bg-gray-900/40"></div>

    <div x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 flex w-72 max-w-[85vw]">
        <button type="button" @click="sidebarOpen = false"
            class="absolute right-2 top-3 z-10 rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900">
            <span class="sr-only">Close menu</span>
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
        @include('layouts.sidebar-content')
    </div>
</div>

<!-- Desktop rail -->
<div class="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-64 lg:flex-col">
    @include('layouts.sidebar-content')
</div>
