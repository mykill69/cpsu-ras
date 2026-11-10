<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected $client;
    protected $driveService;
    protected $rootFolderId;

    public function __construct()
    {
        $credentialPath = storage_path('app/google-credentials.json');
        
        if (!file_exists($credentialPath)) {
            throw new \Exception('Google credentials file not found at: ' . $credentialPath);
        }

        $this->client = new Client();
        $this->client->setAuthConfig($credentialPath);
        $this->client->addScope(Drive::DRIVE_FILE);
        $this->client->setSubject('cpsuras@cpsu.edu.ph');
        $this->client->setHttpClient(new GuzzleClient([
            'verify' => false,
        ]));
        
        $this->driveService = new Drive($this->client);
        $this->rootFolderId = env('GOOGLE_DRIVE_FOLDER_ID');
    }

    public function getOrCreateFolder($folderName, $parentId = null)
    {
        $parentId = $parentId ?: $this->rootFolderId;
        
        $escapedName = str_replace("'", "\\'", $folderName);
        $query = "mimeType='application/vnd.google-apps.folder' and name='{$escapedName}' and trashed=false and '{$parentId}' in parents";
        
        try {
            $files = $this->driveService->files->listFiles([
                'q' => $query,
                'spaces' => 'drive',
                'fields' => 'files(id, name)',
            ]);

            if (count($files->files) > 0) {
                return $files->files[0]->id;
            }
        } catch (\Exception $e) {
            Log::error('Google Drive folder lookup failed: ' . $e->getMessage());
        }

        $folderMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId],
        ]);

        $folder = $this->driveService->files->create($folderMetadata, ['fields' => 'id']);
        return $folder->id;
    }

    public function uploadFile($filePath, $fileName, $mimeType, $folderStructure = [])
    {
        $parentId = $this->rootFolderId;

        foreach ($folderStructure as $folderName) {
            $parentId = $this->getOrCreateFolder($folderName, $parentId);
        }

        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$parentId],
        ]);

        $content = file_get_contents($filePath);
        
        $file = $this->driveService->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id, name, webViewLink',
        ]);

        return [
            'id' => $file->id,
            'name' => $file->name,
            'link' => $file->webViewLink ?? "https://drive.google.com/file/d/{$file->id}/view",
        ];
    }
}