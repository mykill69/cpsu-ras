<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\File;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DriveController extends Controller
{
    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
        $this->middleware('auth');
    }

//     public function index(Request $request, ?Folder $folder = null)
// {
//     $currentFolder = $folder;
//     $parentId = $folder ? $folder->id : null;
    
//     $foldersQuery = Folder::with(['creator'])
//         ->where('parent_id', $parentId)
//         ->where('id', '!=', $parentId)
//         ->where('is_archived', false);

//     if ($request->has('search') && $request->search) {
//         $search = $request->search;
//         $foldersQuery->where(function($q) use ($search) {
//             $q->where('name', 'like', "%{$search}%")
//               ->orWhere('description', 'like', "%{$search}%");
//         });
//     }

//     $sort = $request->get('sort', 'name');
//     $direction = $request->get('direction', 'asc');
//     $allowedSorts = ['name', 'created_at', 'updated_at'];
//     if (in_array($sort, $allowedSorts)) {
//         $foldersQuery->orderBy($sort, $direction);
//     }

//     $folders = $foldersQuery->paginate(30);
    
//     $filesQuery = File::with(['uploader'])
//         ->where('folder_id', $parentId);
    
//     if ($request->has('search') && $request->search) {
//         $search = $request->search;
//         $filesQuery->where('name', 'like', "%{$search}%");
//     }
    
//     $files = $filesQuery->orderBy($sort ?? 'name', $direction ?? 'asc')->paginate(30);

//     $breadcrumbs = $folder ? $folder->breadcrumb : collect([]);

//     return view('drive.index', compact('folders', 'files', 'currentFolder', 'breadcrumbs'));
// }

public function index(Request $request, ?Folder $folder = null)
{
    $currentFolder = $folder;
    $parentId = $folder ? $folder->id : null;
    
    $foldersQuery = Folder::with(['creator'])
        ->withCount(['files as files_count', 'children as subfolders_count'])
        ->where('parent_id', $parentId)
        ->where('id', '!=', $parentId)
        ->where('is_archived', false);

    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $foldersQuery->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    $sort = $request->get('sort', 'name');
    $direction = $request->get('direction', 'asc') === 'desc' ? 'desc' : 'asc';
    $allowedSorts = ['name', 'created_at', 'updated_at'];
    if (in_array($sort, $allowedSorts)) {
        $foldersQuery->orderBy($sort, $direction);
    }

    // children_count is an appended accessor that runs one query per folder;
    // the withCount above already provides the numbers in a single query.
    $folders = $foldersQuery->paginate(30);
    $folders->getCollection()->each->setAppends([]);
    
    $filesQuery = File::with(['uploader'])
        ->where('folder_id', $parentId);
    
    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $filesQuery->where('name', 'like', "%{$search}%");
    }
    
    // Determine sort field for files (avoid sorting by folder-only fields)
    $fileSort = in_array($sort, $allowedSorts) ? $sort : 'name';
    $files = $filesQuery->orderBy($fileSort, $direction)->paginate(30);

    $breadcrumbs = $folder ? $folder->breadcrumb : collect([]);

    return view('drive.index', compact('folders', 'files', 'currentFolder', 'breadcrumbs'));
}



/**
 * Live search used by the Drive search bar.
 *
 * Searches folder names and file names inside the given folder and everything
 * below it (or the whole drive when no folder is given). Each result carries a
 * "location" path relative to where the search started.
 */
public function search(Request $request)
{
    $validated = $request->validate([
        'q' => 'required|string|max:255',
        'folder_id' => 'nullable|integer|exists:folders,id',
    ]);

    $term = trim($validated['q']);
    $startId = isset($validated['folder_id']) ? (int) $validated['folder_id'] : null;
    $like = '%' . addcslashes($term, '%_\\') . '%';
    $limit = 60;

    // One query for the folder tree, then walk it in memory.
    $tree = Folder::where('is_archived', false)->get(['id', 'name', 'parent_id']);
    $byId = $tree->keyBy('id');
    $childrenOf = $tree->groupBy(fn ($f) => $f->parent_id ?? 0);

    $scopeIds = [];
    $stack = [$startId ?? 0];
    while ($stack) {
        $id = array_pop($stack);
        foreach ($childrenOf->get($id, collect()) as $child) {
            $scopeIds[] = $child->id;
            $stack[] = $child->id;
        }
    }

    // Folder path relative to the starting folder, e.g. "Reports / 2026"
    $locationOf = function (?int $folderId) use ($byId, $startId) {
        $parts = [];
        $guard = 0;
        while ($folderId && $folderId !== $startId && $guard++ < 50) {
            $folder = $byId->get($folderId);
            if (!$folder) break;
            array_unshift($parts, $folder->name);
            $folderId = $folder->parent_id;
        }
        return $parts ? implode(' / ', $parts) : null;
    };

    $folders = Folder::withCount(['files as files_count', 'children as subfolders_count'])
        ->whereIn('id', $scopeIds ?: [0])
        ->where('name', 'like', $like)
        ->orderBy('name')
        ->limit($limit)
        ->get()
        ->each->setAppends([])
        ->map(function ($folder) use ($locationOf) {
            $folder->location = $locationOf($folder->parent_id);
            return $folder;
        });

    $fileScope = $scopeIds;
    if ($startId) {
        $fileScope[] = $startId;
    }

    $files = File::where(function ($q) use ($like) {
            $q->where('original_name', 'like', $like)->orWhere('name', 'like', $like);
        })
        ->where(function ($q) use ($fileScope, $startId) {
            $q->whereIn('folder_id', $fileScope ?: [0]);
            if (!$startId) {
                $q->orWhereNull('folder_id');
            }
        })
        ->orderBy('original_name')
        ->limit($limit)
        ->get()
        ->map(function ($file) use ($locationOf) {
            $file->location = $locationOf($file->folder_id);
            return $file;
        });

    return response()->json([
        'query' => $term,
        'folders' => $folders->values(),
        'files' => $files->values(),
        'truncated' => $folders->count() >= $limit || $files->count() >= $limit,
    ]);
}

public function getBreadcrumbAttribute()
{
    $breadcrumb = collect([]);
    $folder = $this;
    $maxDepth = 10; // Safety limit
    
    while ($folder && $maxDepth > 0) {
        $breadcrumb->prepend($folder);
        $folder = $folder->parent;
        $maxDepth--;
    }
    
    return $breadcrumb;
}

    public function createFolder(Request $request)
    {
         $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'parent_id' => 'nullable|exists:folders,id',
        'color' => 'nullable|string|max:7',
    ]);

    // Check for duplicate folder name in the same parent
    $exists = Folder::where('parent_id', $validated['parent_id'] ?? null)
        ->where('name', $validated['name'])
        ->exists();
    
    if ($exists) {
        return response()->json([
            'success' => false,
            'message' => 'A folder with this name already exists here.'
        ], 422);
    }

        try {
            DB::beginTransaction();

            $folder = Folder::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'parent_id' => $validated['parent_id'] ?? null,
                'color' => $validated['color'] ?? '#4285f4',
            ]);

            activity()
                ->performedOn($folder)
                ->causedBy(auth()->user())
                ->withProperties(['action' => 'created', 'name' => $folder->name])
                ->log('Folder created');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Folder created successfully',
                'folder' => $folder
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Folder creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create folder'], 500);
        }
    }

    public function uploadFiles(Request $request)
{
    if (!$request->hasFile('files')) {
        return response()->json(['success' => false, 'message' => 'No files uploaded'], 400);
    }

    try {
        $request->validate([
            'files.*' => 'required|file|max:102400',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        DB::beginTransaction();
        $uploadedFiles = [];
        $files = $request->file('files');
        if (!is_array($files)) $files = [$files];
        
        foreach ($files as $uploadedFile) {
            if (!$uploadedFile || !$uploadedFile->isValid()) continue;

            $tempPath = $uploadedFile->store('temp');
            $encryptedData = $this->encryptionService->encryptFile($tempPath);
            Storage::delete($tempPath);
            
            File::create([
                'name' => pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME),
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getMimeType() ?? 'application/octet-stream',
                'extension' => $uploadedFile->getClientOriginalExtension() ?? '',
                'size' => $uploadedFile->getSize(),
                'disk' => 'local',
                'path' => $tempPath,
                'encrypted_path' => $encryptedData['encrypted_path'],
                'checksum_sha256' => $encryptedData['hash'],
                'encryption_key_id' => $encryptedData['encrypted_key'],
                'folder_id' => $request->folder_id,
                'is_encrypted' => true,
                'metadata' => ['iv' => $encryptedData['iv'] ?? null, 'tag' => $encryptedData['tag'] ?? null],
            ]);
            $uploadedFiles[] = $uploadedFile->getClientOriginalName();
        }

        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => count($uploadedFiles) . ' file(s) uploaded successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Upload failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    /**
     * Upload a folder (and any nested sub-folders).
     *
     * The browser sends files in batches. The first batch creates the root
     * folder and gets its id back; later batches pass that id as
     * `root_folder_id` so every file lands in the same tree.
     * `paths[i]` is the file's relative path, e.g. "Reports/2026/Q1/a.pdf".
     */
    public function uploadFolder(Request $request)
{
    $request->validate([
        'folder_name' => 'required|string|max:255',
        'folder_id' => 'nullable|exists:folders,id',
        'root_folder_id' => 'nullable|exists:folders,id',
        'files' => 'required|array',
        'files.*' => 'required|file|max:102400',
        'paths' => 'nullable|array',
        'paths.*' => 'nullable|string|max:1024',
    ]);

    try {
        DB::beginTransaction();

        if ($request->filled('root_folder_id')) {
            $folder = Folder::findOrFail($request->root_folder_id);
        } else {
            // Create the root folder with a unique name inside the current folder
            $parentId = $request->folder_id;
            $baseName = $this->cleanFolderName($request->folder_name);
            $folderName = $baseName;
            $counter = 1;

            while (Folder::where('parent_id', $parentId)
                ->where('name', $folderName)
                ->exists()) {
                $folderName = $baseName . ' (' . $counter . ')';
                $counter++;
            }

            $folder = Folder::create([
                'name' => $folderName,
                'parent_id' => $parentId,
                'department_id' => auth()->user()->department_id,
            ]);

            activity()
                ->performedOn($folder)
                ->causedBy(auth()->user())
                ->withProperties(['action' => 'uploaded', 'name' => $folder->name])
                ->log('Folder uploaded');
        }

        $paths = $request->input('paths', []);
        $folderCache = [];
        $uploadedFiles = [];
        $totalSize = 0;

        foreach ($request->file('files') as $index => $uploadedFile) {
            if (!$uploadedFile || !$uploadedFile->isValid()) continue;

            $fileName = $uploadedFile->getClientOriginalName();
            $targetFolder = $this->resolveSubfolder($folder, $paths[$index] ?? null, $folderCache);

            $tempPath = $uploadedFile->store('temp');
            $encryptedData = $this->encryptionService->encryptFile($tempPath);
            Storage::delete($tempPath);
            
            File::create([
                'name' => pathinfo($fileName, PATHINFO_FILENAME),
                'original_name' => $fileName,
                'mime_type' => $uploadedFile->getMimeType() ?? 'application/octet-stream',
                'extension' => $uploadedFile->getClientOriginalExtension() ?? '',
                'size' => $uploadedFile->getSize(),
                'disk' => 'local',
                'path' => $tempPath,
                'encrypted_path' => $encryptedData['encrypted_path'],
                'checksum_sha256' => $encryptedData['hash'],
                'encryption_key_id' => $encryptedData['encrypted_key'],
                'folder_id' => $targetFolder->id,
                'is_encrypted' => true,
                'metadata' => [
                    'iv' => $encryptedData['iv'] ?? null,
                    'tag' => $encryptedData['tag'] ?? null,
                ],
            ]);

            $uploadedFiles[] = $fileName;
            $totalSize += $uploadedFile->getSize();
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Folder "' . $folder->name . '" uploaded with ' . count($uploadedFiles) . ' file(s)',
            'folder' => ['id' => $folder->id, 'name' => $folder->name],
            'files_count' => count($uploadedFiles),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Folder upload failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to upload folder: ' . $e->getMessage()], 500);
    }
}

    /**
     * Walk a relative path ("Root/Sub/Deeper/file.pdf") below $root, creating any
     * missing sub-folders. The first segment is the root itself and the last is
     * the file name, so only the segments in between become folders.
     */
    private function resolveSubfolder(Folder $root, ?string $relativePath, array &$cache): Folder
    {
        if (!$relativePath) {
            return $root;
        }

        $segments = array_values(array_filter(
            explode('/', str_replace('\\', '/', $relativePath)),
            fn ($s) => $s !== '' && $s !== '.' && $s !== '..'
        ));
        $segments = array_slice($segments, 1, -1);

        $current = $root;
        $key = (string) $root->id;

        foreach ($segments as $segment) {
            $name = $this->cleanFolderName($segment);
            $key .= '/' . $name;

            if (!isset($cache[$key])) {
                $cache[$key] = Folder::firstOrCreate(
                    ['parent_id' => $current->id, 'name' => $name],
                    ['department_id' => auth()->user()->department_id]
                );
            }

            $current = $cache[$key];
        }

        return $current;
    }

    private function cleanFolderName(string $name): string
    {
        $name = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $name));

        return Str::limit($name !== '' ? $name : 'Untitled folder', 255, '');
    }

    public function downloadFile($file)
{
    // Find by UUID or ID
    $file = File::where('uuid', $file)->orWhere('id', $file)->firstOrFail();
    
    try {
        if (!$this->encryptionService->verifyIntegrity($file->encrypted_path, $file->checksum_sha256)) {
            abort(500, 'File integrity check failed');
        }

        $decryptedContent = $this->encryptionService->decryptFile(
            $file->encrypted_path,
            $file->encryption_key_id,
            $file->metadata['iv'],
            $file->metadata['tag']
        );

        $file->recordAccess();
        
        activity()
            ->performedOn($file)
            ->causedBy(auth()->user())
            ->withProperties(['action' => 'downloaded'])
            ->log('File downloaded');

        $tempFile = tempnam(sys_get_temp_dir(), 'download_');
        file_put_contents($tempFile, $decryptedContent);

        return response()->download($tempFile, $file->original_name, [
            'Content-Type' => $file->mime_type,
        ])->deleteFileAfterSend(true);

    } catch (\Exception $e) {
        Log::error('File download failed: ' . $e->getMessage());
        abort(500, 'File download failed');
    }
}

    public function deleteFolder(Folder $folder)
    {
        try {
            $user = Auth::user();

            $hasChildren = $folder->children()->exists();
            $hasFiles = $folder->files()->exists();
            
            if ($hasChildren || $hasFiles) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder is not empty. Please delete all contents first.',
                ], 422);
            }

            DB::beginTransaction();

            $folderName = $folder->name;
            $folder->delete();

            activity()
                ->performedOn($folder)
                ->causedBy($user)
                ->withProperties(['action' => 'deleted', 'name' => $folderName])
                ->log('Folder deleted');

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Folder deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Folder deletion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete folder'], 500);
        }
    }

    public function updateFolder(Request $request, Folder $folder)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder->update(['name' => $validated['name']]);

        return response()->json(['success' => true, 'message' => 'Folder renamed successfully']);
    }

    public function archived()
    {
        $folders = Folder::where('is_archived', true)->paginate(30);
        $files = File::where('status', 'archived')->paginate(30);
        $breadcrumbs = collect([]);
        $currentFolder = null;

        return view('drive.index', compact('folders', 'files', 'currentFolder', 'breadcrumbs'));
    }

    public function archiveFolder(Folder $folder)
    {
        $folder->update(['is_archived' => true, 'archived_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Folder archived']);
    }

    public function unarchiveFolder(Folder $folder)
    {
        $folder->update(['is_archived' => false, 'archived_at' => null]);
        return response()->json(['success' => true, 'message' => 'Folder unarchived']);
    }

    public function deleteFile(File $file)
    {
        $file->delete();
        return response()->json(['success' => true, 'message' => 'File deleted']);
    }

    /**
     * Small preview image for image files.
     *
     * Generated on the fly from the encrypted original and never written to disk
     * unencrypted; the browser caches it privately instead.
     */
    public function thumbnail(Request $request, $file)
    {
        $file = File::where('uuid', $file)->orWhere('id', $file)->firstOrFail();

        $isRaster = $file->mime_type
            && str_starts_with($file->mime_type, 'image/')
            && $file->mime_type !== 'image/svg+xml';

        if (!$isRaster || !$file->encrypted_path || !function_exists('imagecreatefromstring')) {
            abort(404);
        }

        $etag = '"' . substr($file->checksum_sha256 ?: md5($file->uuid), 0, 32) . '-t1"';
        if ($request->header('If-None-Match') === $etag) {
            return response('', 304, ['ETag' => $etag, 'Cache-Control' => 'private, max-age=604800']);
        }

        try {
            $source = @imagecreatefromstring($file->getDecryptedContent());
            if (!$source) {
                abort(404);
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $max = 480;
            $scale = min(1, $max / max($width, $height));
            $newWidth = max(1, (int) round($width * $scale));
            $newHeight = max(1, (int) round($height * $scale));

            $thumb = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            imagefill($thumb, 0, 0, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($source);

            ob_start();
            if (function_exists('imagewebp')) {
                imagewebp($thumb, null, 80);
                $type = 'image/webp';
            } else {
                imagepng($thumb, null, 6);
                $type = 'image/png';
            }
            $data = ob_get_clean();
            imagedestroy($thumb);

            return response($data, 200, [
                'Content-Type' => $type,
                'Cache-Control' => 'private, max-age=604800',
                'ETag' => $etag,
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('Thumbnail failed for file ' . $file->id . ': ' . $e->getMessage());
            abort(404);
        }
    }

    // public function preview(File $file)
    // {
    //     // Check if file is an image or PDF
    //     $isImage = $file->mime_type && str_starts_with($file->mime_type, 'image/');
    //     $isPdf = $file->mime_type === 'application/pdf';
        
    //     if (!$isImage && !$isPdf) {
    //         // For other files, just download
    //         return redirect()->route('drive.files.download', $file->id);
    //     }
        
    //     return view('drive.preview', compact('file', 'isImage', 'isPdf'));
    // }

    public function preview(File $file)
{
    // Check if file is synced to Google Drive
    if ($file->status === 'synced' && isset($file->metadata['google_drive_id'])) {
        // Redirect to Google Drive for synced files
        $driveId = $file->metadata['google_drive_id'];
        $driveLink = $file->metadata['google_drive_link'] ?? "https://drive.google.com/file/d/{$driveId}/view";
        return redirect()->away($driveLink);
    }
    
    // For local files
    $isImage = $file->mime_type && str_starts_with($file->mime_type, 'image/');
    $isPdf = $file->mime_type === 'application/pdf';
    
    if (!$isImage && !$isPdf) {
        // For other files, just download
        return redirect()->route('drive.files.download', $file->id);
    }
    
    return view('drive.preview', compact('file', 'isImage', 'isPdf'));
}

    public function stream($file)
{
    $file = File::where('uuid', $file)->orWhere('id', $file)->firstOrFail();
    
    // Check if file is synced to Google Drive
    if ($file->status === 'synced' && isset($file->metadata['google_drive_id'])) {
        // Redirect to Google Drive for synced files
        $driveId = $file->metadata['google_drive_id'];
        $driveLink = $file->metadata['google_drive_link'] ?? "https://drive.google.com/file/d/{$driveId}/view";
        return redirect()->away($driveLink);
    }
    
    // For local files only
    if (!$file->encrypted_path) {
        abort(404, 'Local file not found. This file has been synced to Google Drive.');
    }
    
    try {
        $decryptedContent = $this->encryptionService->decryptFile(
            $file->encrypted_path,
            $file->encryption_key_id,
            $file->metadata['iv'] ?? '',
            $file->metadata['tag'] ?? ''
        );

        $tempFile = tempnam(sys_get_temp_dir(), 'stream_');
        file_put_contents($tempFile, $decryptedContent);

        return response()->file($tempFile, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $file->original_name . '"',
        ])->deleteFileAfterSend(true);

    } catch (\Exception $e) {
        Log::error('Stream failed: ' . $e->getMessage());
        abort(500, 'Cannot preview file');
    }
}
}
