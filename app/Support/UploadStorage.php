<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Single entry point for every user upload in the portal.
 *
 * Stored values (ActivityMedia::file_path, User::avatar, the portal_logo and
 * default_avatar settings) are disk-relative keys such as
 * "uploads/activities/images/foo.jpg". Legacy rows may carry a leading slash
 * from when uploads were served straight out of public/, so keys are
 * normalised on the way in.
 *
 * ActivityMedia also stores external URLs for "link" and "video" media, so
 * anything that is not an uploads key is passed through untouched.
 *
 * Switching config('uploads.disk') to "s3" relocates every upload without
 * touching the database.
 */
class UploadStorage
{
    public static function disk(): Filesystem
    {
        return Storage::disk(config('uploads.disk'));
    }

    /**
     * A configured upload directory, by name (see config/uploads.php).
     */
    public static function path(string $name): string
    {
        return (string) config("uploads.paths.{$name}");
    }

    /**
     * Normalise a stored value into a disk-relative key.
     */
    public static function key(?string $path): string
    {
        return ltrim((string) $path, '/');
    }

    /**
     * Whether a stored value refers to an object on our disk rather than an
     * external URL pasted in by a user.
     */
    public static function isManaged(?string $path): bool
    {
        return filled($path) && str_starts_with(self::key($path), 'uploads/');
    }

    /**
     * Store an uploaded file and return the value to persist.
     */
    public static function put(UploadedFile $file, string $dir, string $filename): string
    {
        $dir = trim($dir, '/');
        self::disk()->putFileAs($dir, $file, $filename);

        return $dir . '/' . $filename;
    }

    /**
     * Public URL for a stored value, or null when there is nothing to show.
     * External URLs are returned unchanged.
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (! self::isManaged($path)) {
            return $path;
        }

        return self::disk()->url(self::key($path));
    }

    public static function exists(?string $path): bool
    {
        return self::isManaged($path) && self::disk()->exists(self::key($path));
    }

    public static function delete(?string $path): void
    {
        if (self::isManaged($path)) {
            self::disk()->delete(self::key($path));
        }
    }

    /**
     * Raw bytes, or null when the object is missing or external.
     */
    public static function get(?string $path): ?string
    {
        return self::exists($path) ? self::disk()->get(self::key($path)) : null;
    }

    public static function size(?string $path): int
    {
        return self::exists($path) ? (int) self::disk()->size(self::key($path)) : 0;
    }

    public static function mimeType(?string $path): ?string
    {
        return self::exists($path) ? (self::disk()->mimeType(self::key($path)) ?: null) : null;
    }

    /**
     * Stored keys in $dir whose filename begins with "$prefix." -- used to
     * resolve a staged upload token back to its file, whatever its extension.
     */
    public static function findByPrefix(string $dir, string $prefix): array
    {
        return array_values(array_filter(
            self::disk()->files(trim($dir, '/')),
            fn (string $file) => str_starts_with(basename($file), $prefix . '.')
        ));
    }

    /**
     * Move an object within the disk and return the value to persist.
     */
    public static function move(string $from, string $to): string
    {
        $to = self::key($to);
        self::disk()->move(self::key($from), $to);

        return $to;
    }
}
