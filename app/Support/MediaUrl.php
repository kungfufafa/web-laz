<?php

namespace App\Support;

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

        $host = $request->getSchemeAndHttpHost();

        if (Storage::disk('public')->exists($normalized)) {
            return "{$host}/storage/{$normalized}";
        }

        if (Storage::disk('local')->exists($normalized)) {
            return "{$host}/api/media/".self::encodePath($normalized);
        }

        return "{$host}/storage/{$normalized}";
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
}
