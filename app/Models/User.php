<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * Class User
 *
 * @package App\Models
 * 
 * @method bool hasRole(string|array $roles, string $guard = null)
 * @method bool hasAnyRole(array $roles)
 * @method \Spatie\Permission\Contracts\Role assignRole(...$roles)
 * @method \Illuminate\Support\Collection getRoleNames()
 * @method bool hasPermissionTo(string $permission)
 * @method bool isAdministrator()
 * @method bool isRecordsOfficer()
 * @method bool isRecordsStaff()
 * @method bool canManageUsers()
 */
class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, LogsActivity;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'department_id',
        'position',
        'is_active',
        'last_login_at',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Set the user's full name (splits into first_name and last_name).
     */
    public function setNameAttribute($value): void
    {
        $parts = explode(' ', $value, 2);
        $this->attributes['first_name'] = $parts[0];
        $this->attributes['last_name'] = $parts[1] ?? '';
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class, 'created_by');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'uploaded_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Check if user is an administrator.
     */
    public function isAdministrator(): bool
    {
        return $this->hasRole('administrator') || $this->position === 'System Administrator';
    }

    /**
     * Check if user is a records officer.
     */
    public function isRecordsOfficer(): bool
    {
        return $this->hasRole('records_officer') || $this->position === 'Records Officer';
    }

    /**
     * Check if user is records staff.
     */
    public function isRecordsStaff(): bool
    {
        return $this->hasRole('records_staff') || $this->position === 'Records Staff';
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->hasAnyRole(['administrator', 'records_officer'])
            || in_array($this->position, ['System Administrator', 'Records Officer']);
    }

    /**
     * Get user's activities.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class, 'causer_id');
    }
}