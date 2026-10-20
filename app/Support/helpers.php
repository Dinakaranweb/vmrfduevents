<?php

use App\Support\UploadStorage;

if (! function_exists('upload_url')) {
    /**
     * Public URL for a stored upload path. External URLs (pasted photo links
     * and video URLs) are returned unchanged, and null/empty values yield null
     * so views can fall back to a placeholder.
     */
    function upload_url(?string $path): ?string
    {
        return UploadStorage::url($path);
    }
}

if (! function_exists('upload_exists')) {
    /**
     * Whether a stored upload path still resolves to a file on the disk.
     */
    function upload_exists(?string $path): bool
    {
        return UploadStorage::exists($path);
    }
}
