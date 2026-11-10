@forelse($users as $user)
<tr class="hover:bg-primary-50/30 transition-colors" id="user-row-{{ $user->id }}">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-primary-100 flex items-center justify-center text-sm font-semibold text-primary-700">
                {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
            </div>
            <div>
                <p class="text-sm font-medium text-primary-900">{{ $user->name }}</p>
                <p class="text-xs text-primary-400">{{ $user->position }}</p>
            </div>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-600">{{ $user->email }}</td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
            @if($user->hasRole('administrator')) bg-red-100 text-red-700
            @elseif($user->hasRole('records_officer')) bg-blue-100 text-blue-700
            @else bg-green-100 text-green-700 @endif">
            {{ ucfirst(str_replace('_', ' ', $user->roles->first()->name ?? 'N/A')) }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-600">{{ $user->department->name ?? 'N/A' }}</td>
    <td class="px-6 py-4 whitespace-nowrap">
        @if(auth()->id() !== $user->id)
        <button type="button" 
                onclick="toggleUserStatus({{ $user->id }})" 
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none {{ $user->is_active ? 'bg-green-500' : 'bg-gray-300' }}"
                style="cursor: pointer;">
            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $user->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </button>
        @else
        <span class="inline-flex h-6 w-11 items-center rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-gray-300' }} opacity-50 cursor-not-allowed">
            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $user->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
        </span>
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-right">
        <div class="flex items-center justify-end gap-2">
            <button type="button" 
                    onclick='editUserFromTable(@json($user))' 
                    class="p-2 rounded-lg text-primary-400 hover:text-primary-600 hover:bg-primary-50 transition-colors"
                    title="Edit User"
                    style="cursor: pointer;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </button>
            @if(auth()->id() !== $user->id)
            <button type="button" 
                    onclick="deleteUserFromTable({{ $user->id }}, '{{ addslashes($user->name) }}')" 
                    class="p-2 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                    title="Delete User"
                    style="cursor: pointer;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="px-6 py-20 text-center">
        <svg class="w-16 h-16 text-primary-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
        </svg>
        <p class="text-primary-500">No users found</p>
    </td>
</tr>
@endforelse