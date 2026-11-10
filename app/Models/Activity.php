<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends SpatieActivity
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_log';

    /**
     * Get the formatted created date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M d, Y H:i:s');
    }

    /**
     * Get the event badge color.
     */
    public function getEventBadgeColorAttribute(): string
    {
        return match($this->event) {
            'created' => 'bg-green-100 text-green-700',
            'updated' => 'bg-blue-100 text-blue-700',
            'deleted' => 'bg-red-100 text-red-700',
            'uploaded' => 'bg-purple-100 text-purple-700',
            'downloaded' => 'bg-yellow-100 text-yellow-700',
            'synced' => 'bg-indigo-100 text-indigo-700',
            'archived' => 'bg-orange-100 text-orange-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Get the causer's name.
     */
    public function getCauserNameAttribute(): ?string
    {
        if (!$this->causer) {
            return 'System';
        }
        
        return $this->causer->name ?? 'System';
    }

    /**
     * Get the causer's initials for avatar.
     */
    public function getCauserInitialsAttribute(): string
    {
        if (!$this->causer) {
            return 'SYS';
        }
        
        return strtoupper(substr($this->causer->first_name ?? 'S', 0, 1) . substr($this->causer->last_name ?? 'YS', 0, 1));
    }
}