<div
    class="flex grow flex-col gap-y-5 overflow-y-auto bg-gradient-to-b from-primary-700 to-primary-900 px-6 pb-4 border-r border-primary-600/30">
    <!-- Logo -->
    <div class="flex h-16 shrink-0 items-center">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gold-500/20 rounded-xl flex items-center justify-center border border-gold-500/30">
                <svg class="w-6 h-6 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <div>
                <span class="text-lg font-bold text-white">CPSU RAS</span>
                <p class="text-xs text-primary-300">Records Archive System</p>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul role="list" class="-mx-2 space-y-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="sidebar-link group flex gap-x-3 rounded-xl p-2.5 text-sm font-medium leading-6 {{ request()->routeIs('dashboard') ? 'bg-gold-500/20 text-gold-400 border border-gold-500/30' : 'text-primary-200 hover:bg-primary-600/50 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <!-- Drive -->
                    <li>
                        <a href="{{ route('drive.index') }}"
                            class="sidebar-link group flex gap-x-3 rounded-xl p-2.5 text-sm font-medium leading-6 {{ request()->routeIs('drive.*') ? 'bg-gold-500/20 text-gold-400 border border-gold-500/30' : 'text-primary-200 hover:bg-primary-600/50 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                            </svg>
                            My Drive
                        </a>
                    </li>

                    <!-- Backup & Sync -->
                    <li>
                        <a href="{{ route('backup.index') }}"
                            class="sidebar-link group flex gap-x-3 rounded-xl p-2.5 text-sm font-medium leading-6 {{ request()->routeIs('backup.*') ? 'bg-gold-500/20 text-gold-400 border border-gold-500/30' : 'text-primary-200 hover:bg-primary-600/50 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Backup & Sync
                        </a>
                    </li>

                    <!-- Archived -->
                    {{-- <li>
                        <a href="{{ route('drive.archived') }}" 
                           class="sidebar-link group flex gap-x-3 rounded-xl p-2.5 text-sm font-medium leading-6 {{ request()->routeIs('drive.archived') ? 'bg-gold-500/20 text-gold-400 border border-gold-500/30' : 'text-primary-200 hover:bg-primary-600/50 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            Archived
                        </a>
                    </li> --}}

                    <!-- Separator -->
                    <li class="pt-2">
                        <div class="px-2">
                            <div class="border-t border-primary-600/30"></div>
                        </div>
                    </li>

                    <!-- User Management -->
                    @if (auth()->user()->canManageUsers())
                        <li>
                            <a href="{{ route('users.index') }}"
                                class="sidebar-link group flex gap-x-3 rounded-xl p-2.5 text-sm font-medium leading-6 {{ request()->routeIs('users.*') ? 'bg-gold-500/20 text-gold-400 border border-gold-500/30' : 'text-primary-200 hover:bg-primary-600/50 hover:text-white' }}">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                User Management
                            </a>
                        </li>
                    @endif

                    <!-- Activity Logs -->
                    @if (auth()->user()->hasRole(['administrator', 'records_officer']))
                        <li>
                            <a href="{{ route('activity-logs.index') }}"
                                class="sidebar-link group flex gap-x-3 rounded-xl p-2.5 text-sm font-medium leading-6 {{ request()->routeIs('activity-logs.*') ? 'bg-gold-500/20 text-gold-400 border border-gold-500/30' : 'text-primary-200 hover:bg-primary-600/50 hover:text-white' }}">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Activity Logs
                            </a>
                        </li>
                    @endif

                    <!-- Settings -->
                    @if (auth()->user()->hasRole('administrator'))
                        <li>
                            <a href="{{ route('settings.index') }}"
                                class="sidebar-link group flex gap-x-3 rounded-xl p-2.5 text-sm font-medium leading-6 {{ request()->routeIs('settings.*') ? 'bg-gold-500/20 text-gold-400 border border-gold-500/30' : 'text-primary-200 hover:bg-primary-600/50 hover:text-white' }}">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </a>
                        </li>
                    @endif
                </ul>
            </li>

            <!-- User Profile -->
            <li class="mt-auto">
                <div class="flex items-center gap-x-4 px-2 py-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-gold-500/20 border border-gold-500/30 flex items-center justify-center">
                        <span class="text-sm font-semibold text-gold-400">
                            {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-primary-300 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                        @csrf
                        <button type="submit"
                            class="p-1.5 rounded-lg text-primary-400 hover:text-white hover:bg-primary-600/50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>
</div>
