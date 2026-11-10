<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileVersion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_id',
        'path',
        'encrypted_path',
        'size',
        'checksum_sha256',
        'version_number',
        'uploaded_by',
        'change_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'version_number' => 'integer',
    ];

    /**
     * Get the file that owns this version.
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who uploaded this version.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope a query to only include versions for a specific file.
     */
    public function scopeForFile($query, $fileId)
    {
        return $query->where('file_id', $fileId);
    }

    /**
     * Scope a query to get the latest versions.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('version_number', 'desc');
    }

    /**
     * Check if this is the latest version of the file.
     */
    public function isLatest(): bool
    {
        return $this->version_number === $this->file->version;
    }

    /**
     * Get the previous version if it exists.
     */
    public function previousVersion()
    {
        return self::where('file_id', $this->file_id)
            ->where('version_number', '<', $this->version_number)
            ->orderBy('version_number', 'desc')
            ->first();
    }

    /**
     * Get the next version if it exists.
     */
    public function nextVersion()
    {
        return self::where('file_id', $this->file_id)
            ->where('version_number', '>', $this->version_number)
            ->orderBy('version_number', 'asc')
            ->first();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fileVersion) {
            // Auto-increment version number if not set
            if (!$fileVersion->version_number) {
                $latestVersion = self::where('file_id', $fileVersion->file_id)
                    ->max('version_number');
                
                $fileVersion->version_number = ($latestVersion ?? 0) + 1;
            }

            // Set the uploader if not set
            if (!$fileVersion->uploaded_by && auth()->check()) {
                $fileVersion->uploaded_by = auth()->id();
            }
        });

        static::created(function ($fileVersion) {
            // Update the parent file's version number
            $fileVersion->file->update([
                'version' => $fileVersion->version_number,
                'updated_at' => now(),
            ]);
        });
    }
}