<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EncryptionService
{
    private string $cipher = 'aes-256-gcm';
    
    public function __construct()
    {
        // No initialization needed - using Laravel's Crypt facade
    }

    /**
     * Encrypt file content
     */
    public function encryptFile(string $filePath): array
    {
        if (!Storage::disk('local')->exists($filePath)) {
            throw new \Exception('File not found: ' . $filePath);
        }

        $fileContent = Storage::disk('local')->get($filePath);
        
        // Simply encrypt using Laravel's Crypt
        $encryptedContent = Crypt::encryptString($fileContent);
        
        // Store the encrypted file
        $encryptedFileName = 'encrypted/' . Str::uuid() . '.enc';
        Storage::disk('local')->put($encryptedFileName, $encryptedContent);
        
        // Calculate hash for integrity verification
        $hash = hash('sha256', $encryptedContent);
        
        return [
            'encrypted_path' => $encryptedFileName,
            'encrypted_key' => null, // Not needed with Laravel's encryption
            'iv' => null,
            'tag' => null,
            'hash' => $hash,
        ];
    }

    /**
     * Decrypt file content
     */
    public function decryptFile(string $encryptedPath, string $encryptedKey = null, string $iv = null, string $tag = null): string
    {
        if (!Storage::disk('local')->exists($encryptedPath)) {
            throw new \Exception('Encrypted file not found: ' . $encryptedPath);
        }
        
        $encryptedContent = Storage::disk('local')->get($encryptedPath);
        
        try {
            // Use Laravel's Crypt facade for decryption
            return Crypt::decryptString($encryptedContent);
        } catch (\Exception $e) {
            throw new \Exception('Failed to decrypt file: ' . $e->getMessage());
        }
    }

    /**
     * Verify file integrity
     */
    public function verifyIntegrity(string $encryptedPath, string $expectedHash): bool
    {
        if (!Storage::disk('local')->exists($encryptedPath)) {
            return false;
        }
        
        $content = Storage::disk('local')->get($encryptedPath);
        $actualHash = hash('sha256', $content);
        
        return hash_equals($expectedHash, $actualHash);
    }
}