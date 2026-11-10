<nav class="sticky top-0 z-40 bg-gradient-to-r from-primary-700 to-primary-800 border-b border-primary-600/30 backdrop-blur-sm lg:hidden">
    <div class="flex h-16 items-center gap-x-4 px-4 sm:gap-x-6 sm:px-6">
        <button type="button" @click="sidebarOpen = true" class="-m-2.5 p-2.5 text-primary-200 hover:text-white lg:hidden transition-colors">
            <span class="sr-only">Open sidebar</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <div class="flex items-center gap-x-3">
            <div class="w-8 h-8 bg-gold-500/20 rounded-lg flex items-center justify-center border border-gold-500/30">
                <svg class="w-5 h-5 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <span class="text-lg font-bold text-white">CPSU RAS</span>
        </div>

        <div class="flex flex-1 gap-x-4 self-stretch justify-end">
            <!-- Quick Actions -->
            <div class="flex items-center gap-x-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-x-2 text-sm text-primary-200 hover:text-white p-2 rounded-lg hover:bg-primary-600/50 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>