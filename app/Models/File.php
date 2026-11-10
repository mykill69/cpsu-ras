<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'uuid',
        'name',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'disk',
        'path',
        'encrypted_path',
        'checksum_sha256',
        'encryption_key_id',
        'folder_id',
        'uploaded_by',
        'updated_by',
        'is_encrypted',
        'version',
        'status',
        'metadata',
        'description',
        'last_accessed_at',
    ];

    protected $casts = [
        'size' => 'integer',
        'version' => 'integer',
        'is_encrypted' => 'boolean',
        'metadata' => 'array',
        'last_accessed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($file) {
            $file->uuid = (string) Str::uuid();
            $file->uploaded_by = auth()->id();
        });

        static::updating(function ($file) {
            $file->updated_by = auth()->id();
        });
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions()
    {
        return $this->hasMany(FileVersion::class)->orderBy('version_number', 'desc');
    }
    
    // Add this method to get the latest version
    public function latestVersion()
    {
        return $this->hasOne(FileVersion::class)->latestOfMany('version_number');
    }

    // Add this method to create a new version
    public function createNewVersion(array $data): FileVersion
    {
        return $this->versions()->create([
            'path' => $data['path'],
            'encrypted_path' => $data['encrypted_path'] ?? null,
            'size' => $data['size'],
            'checksum_sha256' => $data['checksum_sha256'],
            'change_notes' => $data['change_notes'] ?? null,
        ]);
    }

    // Add this method to revert to a specific version
    public function revertToVersion(FileVersion $targetVersion): void
    {
        $newVersion = $this->createNewVersion([
            'path' => $targetVersion->path,
            'encrypted_path' => $targetVersion->encrypted_path,
            'size' => $targetVersion->size,
            'checksum_sha256' => $targetVersion->checksum_sha256,
            'change_notes' => "Reverted to version {$targetVersion->version_number}",
        ]);

        // Update file metadata
        $this->update([
            'path' => $targetVersion->path,
            'encrypted_path' => $targetVersion->encrypted_path,
            'size' => $targetVersion->size,
            'checksum_sha256' => $targetVersion->checksum_sha256,
        ]);
    }
protected $appends = ['formatted_size', 'icon', 'download_url', 'thumbnail_url'];

    public function getIconAttribute(): string
{
    return $this->getFileIcon($this->mime_type ?? '');
}

    public function getThumbnailUrlAttribute(): ?string
{
    if (!empty($this->mime_type) && str_starts_with($this->mime_type, 'image/')) {
        return route('drive.files.thumbnail', $this->uuid);
    }
    return null;
}



    public function getDownloadUrlAttribute(): string
{
    return route('drive.files.download', $this->uuid);
}

    public function getFormattedSizeAttribute(): string
{
    $bytes = (int)($this->size ?? 0);
    
    if ($bytes === 0) {
        return '0 B';
    }
    
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = (int) floor(log($bytes, 1024));
    
    // Make sure $i is within bounds
    if ($i >= count($units)) {
        $i = count($units) - 1;
    }
    
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}
    public function recordAccess()
    {
        $this->update(['last_accessed_at' => now()]);
        
        activity()
            ->performedOn($this)
            ->withProperties([
                'action' => 'accessed',
                'ip' => request()->ip(),
            ])
            ->log('File accessed');
    }

    public function getDecryptedContent()
{
    if (!$this->is_encrypted) {
        return Storage::disk($this->disk)->get($this->path);
    }

    // Use the encryption service to decrypt
    $encryptionService = app(\App\Services\EncryptionService::class);
    return $encryptionService->decryptFile(
        $this->encrypted_path,
        $this->encryption_key_id,
        $this->metadata['iv'] ?? '',
        $this->metadata['tag'] ?? ''
    );
}
    private function getFileIcon($mimeType): string
{
    if (empty($mimeType)) {
        return 'file';
    }
    
    $icons = [
        'application/pdf' => 'file-pdf',
        'application/msword' => 'file-word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'file-word',
        'application/vnd.ms-excel' => 'file-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'file-excel',
        'application/vnd.ms-powerpoint' => 'file-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'file-powerpoint',
        'application/zip' => 'file-archive',
        'application/x-rar-compressed' => 'file-archive',
    ];

    if (isset($icons[$mimeType])) {
        return $icons[$mimeType];
    }
    
    if (str_starts_with($mimeType, 'image/')) return 'file-image';
    if (str_starts_with($mimeType, 'video/')) return 'file-video';
    if (str_starts_with($mimeType, 'audio/')) return 'file-audio';
    
    return 'file';
}

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function formatBytes($bytes): string
{
    $bytes = (int) $bytes;
    
    if ($bytes === 0) {
        return '0 B';
    }
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $size = $bytes;
    $unitIndex = 0;
    
    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }
    
    return round($size, 2) . ' ' . $units[$unitIndex];
}

}
