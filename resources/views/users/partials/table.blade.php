{{--
    Users table. Rendered with the page and on its own for background
    (AJAX) refreshes. Actions call methods on the userManagement() component.
--}}
@php
    $me = auth()->user();
    $viewerIsAdmin = $me->hasRole('administrator');
    $roleStyles = [
        'administrator' => 'bg-gold-50 text-gold-800 ring-gold-200',
        'records_officer' => 'bg-primary-50 text-primary-800 ring-primary-200',
        'records_staff' => 'bg-gray-100 text-gray-700 ring-gray-200',
    ];
    $sortHeader = function (string $key, string $label) use ($sort, $direction) {
        $active = $sort === $key;
        $aria = $active ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none';
        $icon = $active
            ? ($direction === 'asc'
                ? '<path fill-rule="evenodd" d="M10 5a1 1 0 0 1 .7.3l4 4a1 1 0 1 1-1.4 1.4L10 7.4l-3.3 3.3a1 1 0 0 1-1.4-1.4l4-4A1 1 0 0 1 10 5Z" clip-rule="evenodd" />'
                : '<path fill-rule="evenodd" d="M10 15a1 1 0 0 1-.7-.3l-4-4a1 1 0 1 1 1.4-1.4l3.3 3.3 3.3-3.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-.7.3Z" clip-rule="evenodd" />')
            : '<path d="M10 3.5 13.5 7h-7L10 3.5ZM10 16.5 6.5 13h7L10 16.5Z" />';
        $color = $active ? 'text-primary-800' : 'text-gray-500 hover:text-gray-900';
        $iconColor = $active ? 'text-primary-700' : 'text-gray-300 group-hover:text-gray-500';

        return '<th scope="col" class="px-6 py-3 text-left" aria-sort="' . $aria . '">'
            . '<button type="button" @click="sortBy(\'' . $key . '\')" class="group inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-semibold uppercase tracking-wider ' . $color . '">'
            . e($label) . '<svg class="h-3.5 w-3.5 ' . $iconColor . '" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">' . $icon . '</svg></button></th>';
    };
@endphp

<div hidden data-counts="{{ json_encode($counts ?? null) }}"></div>

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50/70">
            <tr>
                {!! $sortHeader('name', 'User') !!}
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Role</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Department &amp; position</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                {!! $sortHeader('last_login', 'Last sign-in') !!}
                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($users as $user)
                @php
                    $role = $user->roles->first()?->name;
                    $isSelf = $user->is($me);
                    $isDeleted = $user->trashed();
                    // Only administrators may change administrator accounts.
                    $canManage = $viewerIsAdmin || $role !== 'administrator';
                    $initials = strtoupper(mb_substr($user->first_name, 0, 1) . mb_substr($user->last_name, 0, 1));
                @endphp
                <tr class="transition-colors hover:bg-gray-50 {{ $isDeleted ? 'bg-gray-50/60' : '' }}">
                    <td class="px-6 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold {{ $isDeleted || ! $user->is_active ? 'bg-gray-100 text-gray-400' : 'bg-primary-100 text-primary-800' }}">{{ $initials }}</span>
                            <div class="min-w-0">
                                <p class="flex items-center gap-2 text-sm font-medium {{ $isDeleted ? 'text-gray-500 line-through decoration-gray-300' : 'text-gray-900' }}">
                                    <span class="truncate">{{ $user->name }}</span>
                                    @if ($isSelf)
                                        <span class="rounded bg-primary-700 px-1.5 py-px text-[10px] font-semibold uppercase tracking-wide text-white">You</span>
                                    @endif
                                </p>
                                <a href="mailto:{{ $user->email }}" class="block truncate text-xs text-gray-500 hover:text-primary-800 hover:underline">{{ $user->email }}</a>
                            </div>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $roleStyles[$role] ?? 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                            {{ $role ? Str::headline($role) : 'No role' }}
                        </span>
                    </td>
                    <td class="px-6 py-3.5">
                        <p class="whitespace-nowrap text-sm text-gray-800">{{ $user->department->name ?? '—' }}</p>
                        <p class="whitespace-nowrap text-xs text-gray-500">{{ $user->position ?: '—' }}</p>
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5">
                        @if ($isDeleted)
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Deleted {{ $user->deleted_at->diffForHumans() }}
                            </span>
                        @elseif ($isSelf || ! $canManage)
                            <span class="inline-flex items-center gap-2 text-xs font-medium {{ $user->is_active ? 'text-primary-800' : 'text-gray-500' }}"
                                title="{{ $isSelf ? 'You cannot change your own status' : 'Only administrators can change this account' }}">
                                <span class="relative inline-flex h-5 w-9 items-center rounded-full opacity-50 {{ $user->is_active ? 'bg-primary-600' : 'bg-gray-300' }}">
                                    <span class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow {{ $user->is_active ? 'translate-x-[18px]' : 'translate-x-[3px]' }}"></span>
                                </span>
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        @else
                            <button type="button" role="switch" aria-checked="{{ $user->is_active ? 'true' : 'false' }}"
                                aria-label="{{ $user->is_active ? 'Deactivate' : 'Activate' }} {{ $user->name }}"
                                @click="toggleStatus({{ $user->id }}, @js($user->name), {{ $user->is_active ? 'true' : 'false' }})"
                                class="group inline-flex items-center gap-2 rounded-full text-xs font-medium {{ $user->is_active ? 'text-primary-800' : 'text-gray-500' }}">
                                <span class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors {{ $user->is_active ? 'bg-primary-600 group-hover:bg-primary-700' : 'bg-gray-300 group-hover:bg-gray-400' }}">
                                    <span class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform {{ $user->is_active ? 'translate-x-[18px]' : 'translate-x-[3px]' }}"></span>
                                </span>
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        @endif
                    </td>
                    <td class="tabular whitespace-nowrap px-6 py-3.5 text-sm text-gray-600"
                        @if ($user->last_login_at) title="{{ $user->last_login_at->format('M d, Y h:i A') }}" @endif>
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if ($isDeleted)
                                @if ($canManage)
                                    <button type="button" @click="restoreUser({{ $user->id }}, @js($user->name))" class="btn-secondary px-3 py-1.5 text-xs">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7L3 8m0-5v5h5" /></svg>
                                        Restore
                                    </button>
                                @endif
                            @elseif ($canManage)
                                <button type="button" @click="openEdit(@js(\App\Http\Controllers\UserManagementController::payload($user)))"
                                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900" title="Edit {{ $user->name }}" aria-label="Edit {{ $user->name }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                @unless ($isSelf)
                                    <button type="button" @click="deleteUser({{ $user->id }}, @js($user->name))"
                                        class="rounded-lg p-2 text-gray-400 hover:bg-red-50 hover:text-red-700" title="Delete {{ $user->name }}" aria-label="Delete {{ $user->name }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                @endunless
                            @else
                                <span class="px-2 text-xs text-gray-400" title="Only administrators can change administrator accounts">Admin only</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-900">No users match these filters</p>
                        <button type="button" @click="resetFilters()" class="btn-secondary mt-4 text-sm">Reset filters</button>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($users->hasPages())
    <div class="border-t border-gray-200 px-6 py-4">
        {{ $users->links() }}
    </div>
@endif
