@php
    $user = auth()->user();

    // Heroicons (outline, 24px) path data
    $icons = [
        'dashboard' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z',
        'drive' => 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z',
        'backup' => 'M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z',
        'users' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
        'logs' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z',
        'settings' => 'M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75',
    ];

    $workspace = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'dashboard'],
        ['label' => 'My Drive', 'route' => 'drive.index', 'match' => 'drive.*', 'icon' => 'drive'],
        ['label' => 'Backup & Sync', 'route' => 'backup.index', 'match' => 'backup.*', 'icon' => 'backup'],
    ];

    $admin = array_values(array_filter([
        $user->canManageUsers()
            ? ['label' => 'Users', 'route' => 'users.index', 'match' => 'users.*', 'icon' => 'users']
            : null,
        $user->hasRole(['administrator', 'records_officer'])
            ? ['label' => 'Activity Logs', 'route' => 'activity-logs.index', 'match' => 'activity-logs.*', 'icon' => 'logs']
            : null,
        $user->hasRole('administrator')
            ? ['label' => 'Settings', 'route' => 'settings.index', 'match' => 'settings.*', 'icon' => 'settings']
            : null,
    ]));

    $role = $user->getRoleNames()->first();
    $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
@endphp

<div class="flex grow flex-col overflow-y-auto border-r border-gray-200 bg-white">
    <!-- Brand -->
    <a href="{{ route('dashboard') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-100 px-5">
        <img src="{{ asset('images/cpsu-logo.png') }}" alt="" class="h-9 w-9 shrink-0" width="36" height="36">
        <span class="min-w-0 leading-tight">
            <span class="block font-serif text-[17px] font-semibold text-primary-900">CPSU Records</span>
            <span class="block text-[11px] text-gray-500">Archive System</span>
        </span>
    </a>

    <nav class="flex flex-1 flex-col gap-6 px-3 py-5" aria-label="Main">
        <div>
            <p class="eyebrow mb-1.5 px-3">Workspace</p>
            <ul role="list" class="space-y-0.5">
                @foreach ($workspace as $item)
                    <li>
                        <a href="{{ route($item['route']) }}" class="nav-item"
                            @if (request()->routeIs($item['match'])) aria-current="page" @endif>
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}" />
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        @if (count($admin))
            <div>
                <p class="eyebrow mb-1.5 px-3">Administration</p>
                <ul role="list" class="space-y-0.5">
                    @foreach ($admin as $item)
                        <li>
                            <a href="{{ route($item['route']) }}" class="nav-item"
                                @if (request()->routeIs($item['match'])) aria-current="page" @endif>
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}" />
                                </svg>
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </nav>

    <!-- Account -->
    <div class="border-t border-gray-100 p-3">
        <div class="flex items-center gap-3 rounded-lg px-2 py-2">
            <span
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-700 text-xs font-semibold text-white"
                aria-hidden="true">{{ $initials }}</span>
            <div class="min-w-0 flex-1 leading-tight">
                <p class="truncate text-sm font-medium text-gray-900">{{ $user->name }}</p>
                <p class="truncate text-xs text-gray-500">
                    {{ $role ? ucwords(str_replace('_', ' ', $role)) : $user->email }}
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Sign out"
                    class="rounded-md p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-900">
                    <span class="sr-only">Sign out</span>
                    <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
