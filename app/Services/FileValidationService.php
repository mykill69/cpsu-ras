<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class FileValidationService
{
    // Malicious file signatures to block
    protected $blockedSignatures = [
        '4D5A',      // EXE
        '7F454C46',  // ELF
        'CAFEBABE',  // Mach-O
        '3C3F706870', // PHP code
        '3C25',       // ASP
    ];

    protected $maxFileSize = 104857600; // 100MB

    public function validate(UploadedFile $file): bool
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception('File size exceeds maximum allowed size');
        }

        // Check for empty files
        if ($file->getSize() === 0) {
            throw new \Exception('Empty files are not allowed');
        }

        // Scan file content for malicious signatures
        $content = file_get_contents($file->getPathname());
        $hexSignature = strtoupper(bin2hex(substr($content, 0, 4)));
        
        foreach ($this->blockedSignatures as $signature) {
            if (strpos($hexSignature, $signature) === 0) {
                throw new \Exception('This file type is not allowed');
            }
        }

        // Check for PHP or script code in files
        if ($this->containsScriptCode($content)) {
            throw new \Exception('File appears to contain potentially malicious code');
        }

        return true;
    }

    protected function containsScriptCode(string $content): bool
    {
        $patterns = [
            '/<\?php/i',
            '/<script/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/passthru\s*\(/i',
            '/shell_exec\s*\(/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }
}