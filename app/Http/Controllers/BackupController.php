<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    protected $googleDrive;

    public function __construct()
    {
        $this->middleware('auth');
        $this->googleDrive = app(GoogleDriveService::class);
    }

    public function index()
    {
        $files = File::with(['uploader', 'folder'])
            ->where('status', '!=', 'synced')
            ->latest()
            ->paginate(20);

        $totalFiles = File::count();
        $totalSize = File::sum('size');
        $syncedCount = File::where('status', 'synced')->count();

        return view('backup.index', compact('files', 'totalFiles', 'totalSize', 'syncedCount'));
    }

    public function sync(File $file)
{
    try {
        $encryptionService = app(\App\Services\EncryptionService::class);
        $decryptedContent = $encryptionService->decryptFile(
            $file->encrypted_path,
            $file->encryption_key_id,
            $file->metadata['iv'] ?? '',
            $file->metadata['tag'] ?? ''
        );

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempPath = $tempDir . '/' . $file->original_name;
        file_put_contents($tempPath, $decryptedContent);

        $folderStructure = [];
        if ($file->folder) {
            $currentFolder = $file->folder;
            $breadcrumbs = [];
            while ($currentFolder) {
                $breadcrumbs[] = $currentFolder->name;
                $currentFolder = $currentFolder->parent;
            }
            $folderStructure = array_reverse($breadcrumbs);
        }

        $result = $this->googleDrive->uploadFile(
            $tempPath,
            $file->original_name,
            $file->mime_type,
            $folderStructure
        );

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        // Delete local encrypted file
        if ($file->encrypted_path && Storage::disk('local')->exists($file->encrypted_path)) {
            Storage::disk('local')->delete($file->encrypted_path);
        }

        $existingMetadata = is_string($file->metadata) ? json_decode($file->metadata, true) : ($file->metadata ?? []);
        $newMetadata = array_merge($existingMetadata, [
            'google_drive_id' => $result['id'],
            'google_drive_link' => $result['link'],
            'synced_at' => now()->toISOString(),
        ]);

        $file->update([
            'status' => 'synced',
            'metadata' => $newMetadata,
            'encrypted_path' => null,
        ]);

        Log::info('File synced and local copy removed', ['file_id' => $file->id]);

        activity()
            ->performedOn($file)
            ->causedBy(auth()->user())
            ->withProperties(['action' => 'google_drive_synced', 'drive_id' => $result['id']])
            ->log('File synced to Google Drive');

        return response()->json([
            'success' => true,
            'message' => 'File synced to Google Drive and local copy removed'
        ]);
    } catch (\Exception $e) {
        Log::error('Google Drive sync failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Sync failed: ' . $e->getMessage()
        ], 500);
    }
}

        public function syncAll()
{
    $files = File::where('status', '!=', 'synced')->get();
    $synced = 0;
    $failed = 0;

    foreach ($files as $file) {
        try {
            $encryptionService = app(\App\Services\EncryptionService::class);
            $decryptedContent = $encryptionService->decryptFile(
                $file->encrypted_path,
                $file->encryption_key_id,
                $file->metadata['iv'] ?? '',
                $file->metadata['tag'] ?? ''
            );

            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tempPath = $tempDir . '/' . $file->original_name;
            file_put_contents($tempPath, $decryptedContent);

            $folderStructure = [];
            if ($file->folder) {
                $currentFolder = $file->folder;
                $breadcrumbs = [];
                while ($currentFolder) {
                    $breadcrumbs[] = $currentFolder->name;
                    $currentFolder = $currentFolder->parent;
                }
                $folderStructure = array_reverse($breadcrumbs);
            }

            $result = $this->googleDrive->uploadFile(
                $tempPath,
                $file->original_name,
                $file->mime_type,
                $folderStructure
            );

            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            // Delete local encrypted file
            if ($file->encrypted_path && Storage::disk('local')->exists($file->encrypted_path)) {
                Storage::disk('local')->delete($file->encrypted_path);
            }

            $existingMetadata = is_string($file->metadata) ? json_decode($file->metadata, true) : ($file->metadata ?? []);
            $newMetadata = array_merge($existingMetadata, [
                'google_drive_id' => $result['id'],
                'google_drive_link' => $result['link'],
                'synced_at' => now()->toISOString(),
            ]);

            $file->update([
                'status' => 'synced',
                'metadata' => $newMetadata,
                'encrypted_path' => null,
            ]);
            $synced++;
        } catch (\Exception $e) {
            $failed++;
            Log::error('Sync failed for file ' . $file->id . ': ' . $e->getMessage());
        }
    }

    return response()->json([
        'success' => true,
        'message' => $synced . ' file(s) synced, ' . $failed . ' failed'
    ]);
}

    public function synced()
{
    $files = File::with(['uploader', 'folder'])
        ->where('status', 'synced')
        ->latest()
        ->paginate(20);

    return view('backup.synced', compact('files'));
}
}