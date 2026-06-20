<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;

class EncryptionHelper
{
    public static function encryptContent(string $content): string
    {
        return Crypt::encryptString($content);
    }

    public static function decryptContent(string $encryptedContent): string
    {
        return Crypt::decryptString($encryptedContent);
    }

    public static function storeEncrypted(string $path, string $content): bool
    {
        $encrypted = self::encryptContent($content);
        $fullPath = storage_path('app/' . $path);
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($fullPath, $encrypted) !== false;
    }

    public static function readEncrypted(string $path): string
    {
        $content = file_get_contents(storage_path('app/' . $path));
        return self::decryptContent($content);
    }
}
