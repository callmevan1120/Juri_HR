<?php

namespace App\Support;

class AttachmentPathValidator
{
    public function isSafeRelativePath(string $path): bool
    {
        if ($path !== trim($path)) {
            return false;
        }

        $path = trim($path);

        return ! str_starts_with($path, '/')
            && ! str_contains($path, '..')
            && ! str_contains($path, '://')
            && ! str_contains($path, '\\')
            && ! str_contains($path, "\0")
            && preg_match('/[\x00-\x1F\x7F]/', $path) !== 1
            && preg_match('/^[a-zA-Z]:[\\\\\\/]/', $path) !== 1;
    }
}
