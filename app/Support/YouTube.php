<?php

namespace App\Support;

final class YouTube
{
    public static function extractVideoId(?string $value): ?string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        // Already a plain YouTube video ID.
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $raw) === 1) {
            return $raw;
        }

        $normalized = preg_match('/^https?:\/\//i', $raw) === 1 ? $raw : "https://{$raw}";
        $parts = parse_url($normalized);

        if ($parts === false) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');

        if ($host !== '' && str_contains($host, 'youtu.be')) {
            $candidate = explode('/', $path)[0] ?? '';

            return self::sanitizeCandidate($candidate);
        }

        if ($host !== '' && (str_contains($host, 'youtube.com') || str_contains($host, 'youtube-nocookie.com'))) {
            parse_str($parts['query'] ?? '', $query);

            if (isset($query['v']) && is_string($query['v'])) {
                $id = self::sanitizeCandidate($query['v']);

                if ($id !== null) {
                    return $id;
                }
            }

            $segments = array_values(array_filter(explode('/', $path)));
            if (count($segments) >= 2 && in_array($segments[0], ['embed', 'shorts', 'live', 'v'], true)) {
                return self::sanitizeCandidate($segments[1]);
            }
        }

        if (preg_match('~(?:v=|/)([A-Za-z0-9_-]{11})(?:[?&/#]|$)~', $raw, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    private static function sanitizeCandidate(string $candidate): ?string
    {
        $clean = trim($candidate);

        if ($clean === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $clean) === 1) {
            return $clean;
        }

        if (preg_match('/^([A-Za-z0-9_-]{11})/', $clean, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
