@forelse($activities as $activity)
<tr class="hover:bg-primary-50/30 transition-colors">
    <td class="px-6 py-4">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    @if($activity->event === 'created')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    @elseif($activity->event === 'updated')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    @elseif($activity->event === 'deleted')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    @elseif($activity->event === 'uploaded')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    @elseif($activity->event === 'downloaded')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    @endif
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-primary-900">{{ $activity->description }}</p>
                <p class="text-xs text-primary-400 mt-0.5">{{ ucfirst($activity->log_name) }}</p>
            </div>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        @if($activity->causer)
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center text-xs font-medium text-primary-700">
                    {{ strtoupper(substr($activity->causer->first_name ?? 'S', 0, 1) . substr($activity->causer->last_name ?? 'YS', 0, 1)) }}
                </div>
                <span class="text-sm text-primary-600">{{ $activity->causer->name }}</span>
            </div>
        @else
            <span class="text-sm text-primary-400">System</span>
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
            @if($activity->event === 'created') bg-green-100 text-green-700
            @elseif($activity->event === 'updated') bg-blue-100 text-blue-700
            @elseif($activity->event === 'deleted') bg-red-100 text-red-700
            @elseif($activity->event === 'uploaded') bg-purple-100 text-purple-700
            @elseif($activity->event === 'downloaded') bg-yellow-100 text-yellow-700
            @else bg-gray-100 text-gray-700 @endif">
            {{ ucfirst($activity->event ?? 'Unknown') }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-600">
        {{ $activity->created_at->format('M d, Y H:i:s') }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-right">
        <div class="flex items-center justify-end gap-2">
            <button type="button" 
                    onclick="activityLogManager().viewDetails({{ $activity->id }})"
                    class="p-2 rounded-lg text-primary-400 hover:text-primary-600 hover:bg-primary-50 transition-colors"
                    title="View Details"
                    style="cursor: pointer;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
            @if(auth()->user()->canManageUsers())
            <button type="button" 
                    onclick="activityLogManager().deleteLog({{ $activity->id }})"
                    class="p-2 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                    title="Delete Log"
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
    <td colspan="5" class="px-6 py-20 text-center">
        <svg class="w-16 h-16 text-primary-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p class="text-primary-500">No activity logs found</p>
    </td>
</tr>
@endforelse