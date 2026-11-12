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

    public function index(Request $request)
    {
        if ($redirect = $this->rememberListState($request, 'index')) {
            return $redirect;
        }

        [$files, $sort, $direction, $search] = $this->fileList(
            'index',
            $request,
            File::where('status', '!=', 'synced'),
            ['name' => 'original_name', 'size' => 'size', 'type' => 'extension', 'uploaded' => 'created_at'],
            'uploaded'
        );

        if ($request->ajax()) {
            return view('backup.partials.pending-table', compact('files', 'sort', 'direction', 'search'));
        }

        $pendingTotal = File::where('status', '!=', 'synced')->count();

        $totalFiles = File::count();
        $totalSize = File::sum('size');
        $syncedCount = File::where('status', 'synced')->count();

        return view('backup.index', compact('files', 'totalFiles', 'totalSize', 'syncedCount', 'sort', 'direction', 'search', 'pendingTotal'));
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

    public function synced(Request $request)
    {
        if ($redirect = $this->rememberListState($request, 'synced')) {
            return $redirect;
        }

        [$files, $sort, $direction, $search] = $this->fileList(
            'synced',
            $request,
            File::where('status', 'synced'),
            ['name' => 'original_name', 'size' => 'size', 'type' => 'extension', 'synced' => 'updated_at'],
            'synced'
        );

        if ($request->ajax()) {
            return view('backup.partials.synced-table', compact('files', 'sort', 'direction', 'search'));
        }

        $total = File::where('status', 'synced')->count();

        return view('backup.synced', compact('files', 'sort', 'direction', 'search', 'total'));
    }

    /**
     * Search and sort choices arrive as ?q= / ?sort= / ?direction=, get saved in
     * the session. A normal page load is then redirected back to the clean URL
     * (/backup, /backup/synced); background requests carry on (returns null).
     */
    private function rememberListState(Request $request, string $list): ?\Illuminate\Http\RedirectResponse
    {
        if (! $request->hasAny(['q', 'sort', 'direction', 'reset'])) {
            return null;
        }

        $key = "backup.list.$list";

        if ($request->has('reset')) {
            session()->forget($key);
        } else {
            $state = session($key, []);
            foreach (['q', 'sort', 'direction'] as $field) {
                if ($request->has($field)) {
                    $state[$field] = mb_substr(trim((string) $request->query($field)), 0, 100);
                }
            }
            session([$key => $state]);
        }

        // Background (AJAX) requests just get the refreshed table back; a normal
        // page load is sent to page 1 of the clean URL.
        return $request->ajax() ? null : redirect()->to($request->url());
    }

    /**
     * Search + sort + paginate a file list using the choices saved in the
     * session for this list. Only whitelisted columns can be sorted.
     *
     * @return array{0: \Illuminate\Pagination\LengthAwarePaginator, 1: string, 2: string, 3: string}
     */
    private function fileList(string $list, Request $request, $query, array $sortable, string $defaultSort): array
    {
        $state = session("backup.list.$list", []);

        $sort = array_key_exists($state['sort'] ?? '', $sortable) ? $state['sort'] : $defaultSort;
        $direction = ($state['direction'] ?? '') === 'asc' ? 'asc' : 'desc';
        $search = (string) ($state['q'] ?? '');

        $files = $query->with(['uploader', 'folder'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('original_name', 'like', $like)
                        ->orWhere('extension', 'like', $like)
                        ->orWhereHas('uploader', fn ($u) => $u
                            ->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]));
                });
            })
            ->orderBy($sortable[$sort], $direction)
            ->orderBy('id', $direction)
            ->paginate(20);

        return [$files, $sort, $direction, $search];
    }
}
