<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\File;
use App\Models\Department;

class Folder extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'department_id',
        'color',
        'icon',
        'is_archived',
        'archived_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];
    protected $appends = ['children_count'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($folder) {
            $folder->slug = Str::slug($folder->name) . '-' . Str::random(6);
            $folder->created_by = auth()->id();
        });

        static::updating(function ($folder) {
            $folder->updated_by = auth()->id();
        });
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function allFiles()
    {
        $files = $this->files()->get();
        
        foreach ($this->children as $child) {
            $files = $files->merge($child->allFiles());
        }
        
        return $files;
    }

    public function getBreadcrumbAttribute()
    {
        $breadcrumb = collect([]);
        $folder = $this;
        
        while ($folder) {
            $breadcrumb->prepend($folder);
            $folder = $folder->parent;
        }
        
        return $breadcrumb;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getChildrenCountAttribute(): int
{
    return $this->children()->count();
}
}