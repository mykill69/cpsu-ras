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
    $direction = $request->get('direction', 'asc');
    $allowedSorts = ['name', 'created_at', 'updated_at'];
    if (in_array($sort, $allowedSorts)) {
        $foldersQuery->orderBy($sort, $direction);
    }

    $folders = $foldersQuery->paginate(30);
    
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

    public function uploadFolder(Request $request)
{
    $request->validate([
        'folder_name' => 'required|string|max:255',
        'parent_id' => 'nullable|exists:folders,id',
        'files.*' => 'required|file|max:102400',
    ]);

    try {
        DB::beginTransaction();

        // ALWAYS create a new folder with unique name
        $baseName = $request->folder_name;
        $folderName = $baseName;
        $counter = 1;
        
        while (Folder::where('parent_id', $request->parent_id)
            ->where('name', $folderName)
            ->exists()) {
            $folderName = $baseName . ' (' . $counter . ')';
            $counter++;
        }

        $folder = Folder::create([
            'name' => $folderName,
            'parent_id' => $request->parent_id,
            'department_id' => auth()->user()->department_id,
        ]);

        $uploadedFiles = [];
        $totalSize = 0;
        
        foreach ($request->file('files') as $index => $uploadedFile) {
            $fileName = $uploadedFile->getClientOriginalName();
            
            $tempPath = $uploadedFile->store('temp');
            $encryptedData = $this->encryptionService->encryptFile($tempPath);
            Storage::delete($tempPath);
            
            File::create([
                'name' => pathinfo($fileName, PATHINFO_FILENAME),
                'original_name' => $fileName,
                'mime_type' => $uploadedFile->getMimeType(),
                'extension' => $uploadedFile->getClientOriginalExtension(),
                'size' => $uploadedFile->getSize(),
                'disk' => 'local',
                'path' => $tempPath,
                'encrypted_path' => $encryptedData['encrypted_path'],
                'checksum_sha256' => $encryptedData['hash'],
                'encryption_key_id' => $encryptedData['encrypted_key'],
                'folder_id' => $folder->id,
                'is_encrypted' => true,
                'metadata' => [
                    'iv' => $encryptedData['iv'],
                    'tag' => $encryptedData['tag'],
                ],
            ]);

            $uploadedFiles[] = $fileName;
            $totalSize += $uploadedFile->getSize();
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Folder "' . $folder->name . '" uploaded with ' . count($uploadedFiles) . ' file(s)',
            'folder' => $folder->load('children', 'files'),
            'files_count' => count($uploadedFiles),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Folder upload failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to upload folder: ' . $e->getMessage()], 500);
    }
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

    public function thumbnail(File $file)
    {
        // Return file thumbnail if available
        abort(404);
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
