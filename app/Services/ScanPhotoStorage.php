<?php

namespace App\Services;

use App\Models\UserAiRecipeLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Where a scan photo lives, and when it stops living there.
 *
 * On the private disk, never the public one — this is a picture of the inside
 * of someone's fridge, and a guessable URL is the wrong default for that. It is
 * read back out through an authenticated route instead.
 *
 * The path travels in request_meta, so the queue payload carries a string
 * rather than a megabyte of base64 on every attempt.
 */
class ScanPhotoStorage
{
    private const DISK = 'local';

    /** Mime types the model can read and we are willing to accept. */
    public const ACCEPTED_MIMES = 'image/jpeg,image/png,image/webp,image/heic,image/heif';

    /** @return array{path: string, mime: string} for request_meta */
    public function store(UploadedFile $file, int $userId): array
    {
        return [
            'path' => $file->store("scans/{$userId}", self::DISK),
            // Kept rather than assumed: the client downscales to JPEG, but the
            // fallback path uploads whatever the camera produced, and telling
            // the model the wrong type is a silent way to get nothing back.
            'mime' => $file->getMimeType() ?: 'image/jpeg',
        ];
    }

    public function path(UserAiRecipeLog $log): ?string
    {
        $path = $log->request_meta['photo_path'] ?? null;

        return is_string($path) && $path !== '' ? $path : null;
    }

    public function mime(UserAiRecipeLog $log): string
    {
        $mime = $log->request_meta['photo_mime'] ?? null;

        return is_string($mime) && $mime !== '' ? $mime : 'image/jpeg';
    }

    public function exists(UserAiRecipeLog $log): bool
    {
        $path = $this->path($log);

        return $path !== null && Storage::disk(self::DISK)->exists($path);
    }

    /** Raw bytes, or null once the photo has been released. */
    public function contents(UserAiRecipeLog $log): ?string
    {
        $path = $this->path($log);

        if ($path === null) return null;

        return Storage::disk(self::DISK)->get($path);
    }

    /** Absolute path for streaming a response. */
    public function absolutePath(UserAiRecipeLog $log): ?string
    {
        $path = $this->path($log);

        return $path === null ? null : Storage::disk(self::DISK)->path($path);
    }

    /**
     * Best effort, and safe to call twice — it runs from refund paths whose
     * real job is the money, and from a sweep that may race the user.
     */
    public function delete(UserAiRecipeLog $log): void
    {
        $path = $this->path($log);

        if ($path !== null) $this->deletePath($path);
    }

    /**
     * For the one case with no log to go through: an upload whose row never
     * got written, and which nothing would ever sweep.
     */
    public function deletePath(string $path): void
    {
        try {
            Storage::disk(self::DISK)->delete($path);
        } catch (\Throwable) {
            // A photo that outlives its scan is swept later; a cleanup that
            // throws here would take a refund down with it.
        }
    }
}
