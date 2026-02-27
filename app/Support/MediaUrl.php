<?php

namespace App\Support;

use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class MediaUrl
{
    public static function resolve(Request $request, ?string $path): ?string
    {
        $value = trim((string) $path);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value) === 1) {
            return $value;
        }

        $normalized = self::normalizePath($value);
        if ($normalized === '') {
            return null;
        }

        if (self::isProtectedPath($normalized)) {
            return null;
        }

        $host = $request->getSchemeAndHttpHost();

        if (Storage::disk('public')->exists($normalized)) {
            return "{$host}/storage/{$normalized}";
        }

        if (self::isWhitelistedLocalPath($normalized) && Storage::disk('local')->exists($normalized)) {
            return "{$host}/api/media/".self::encodePath($normalized);
        }

        return null;
    }

    public static function isProtectedPath(string $path): bool
    {
        $normalized = self::normalizePath($path);
        $protectedDirectory = Donation::PROOF_IMAGE_DIRECTORY;

        if ($protectedDirectory === '') {
            return false;
        }

        return $normalized === $protectedDirectory || str_starts_with($normalized, $protectedDirectory.'/');
    }

    public static function isWhitelistedLocalPath(string $path): bool
    {
        $normalized = self::normalizePath($path);

        foreach (self::whitelistedLocalPaths() as $allowedPath) {
            if ($normalized === $allowedPath || str_starts_with($normalized, $allowedPath.'/')) {
                return true;
            }
        }

        return false;
    }

    public static function normalizePath(string $path): string
    {
        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        return ltrim($normalized, '/');
    }

    public static function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    /**
     * @return array<int, string>
     */
    private static function whitelistedLocalPaths(): array
    {
        return array_values(array_filter(
            array_map(
                static fn (mixed $path): string => trim((string) $path, '/'),
                (array) config('media.allowed_local_paths', []),
            ),
            static fn (string $path): bool => $path !== '',
        ));
    }
}
