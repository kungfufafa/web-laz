<?php

namespace App\Services;

use App\Support\YouTube;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class YouTubeMetadataService
{
    /**
     * @return array{youtube_id: string, title: ?string, description: ?string, thumbnail: ?string}|null
     */
    public function fetch(?string $value): ?array
    {
        $youtubeId = YouTube::extractVideoId($value);

        if (! $youtubeId) {
            return null;
        }

        $watchUrl = "https://www.youtube.com/watch?v={$youtubeId}";
        $title = null;
        $description = null;
        $thumbnail = null;

        try {
            $oEmbedResponse = Http::timeout(8)
                ->acceptJson()
                ->get('https://www.youtube.com/oembed', [
                    'url' => $watchUrl,
                    'format' => 'json',
                ]);

            if ($oEmbedResponse->successful()) {
                $payload = $oEmbedResponse->json();
                $title = $this->clean((string) ($payload['title'] ?? ''));
                $thumbnail = $this->clean((string) ($payload['thumbnail_url'] ?? ''));
            }
        } catch (\Throwable) {
            // Ignore network failures and continue to other sources/fallback.
        }

        try {
            $watchResponse = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; LAZBot/1.0; +https://laz.local)',
                    'Accept-Language' => 'id,en-US;q=0.9,en;q=0.8',
                ])
                ->get($watchUrl);

            if ($watchResponse->successful()) {
                $html = $watchResponse->body();

                $title ??= $this->extractMetaTagContent($html, 'property', 'og:title');
                $title ??= $this->extractMetaTagContent($html, 'name', 'title');

                $description ??= $this->extractMetaTagContent($html, 'property', 'og:description');
                $description ??= $this->extractMetaTagContent($html, 'name', 'description');

                $thumbnail ??= $this->extractMetaTagContent($html, 'property', 'og:image');
            }
        } catch (\Throwable) {
            // Ignore network failures and continue with fallback data.
        }

        $title = $this->normalizeTitle($title);
        $description = $this->clean($description);
        $thumbnail = $this->clean($thumbnail) ?? "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg";

        return [
            'youtube_id' => $youtubeId,
            'title' => $title,
            'description' => $description,
            'thumbnail' => $thumbnail,
        ];
    }

    private function extractMetaTagContent(string $html, string $attribute, string $value): ?string
    {
        $attributePattern = preg_quote($attribute, '/');
        $valuePattern = preg_quote($value, '/');

        $patterns = [
            // <meta property="og:title" content="...">
            "/<meta\\s+[^>]*{$attributePattern}=[\"']{$valuePattern}[\"'][^>]*content=[\"']([^\"']+)[\"'][^>]*>/i",
            // <meta content="..." property="og:title">
            "/<meta\\s+[^>]*content=[\"']([^\"']+)[\"'][^>]*{$attributePattern}=[\"']{$valuePattern}[\"'][^>]*>/i",
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches) === 1) {
                return $this->clean($matches[1] ?? null);
            }
        }

        return null;
    }

    private function normalizeTitle(?string $title): ?string
    {
        $cleanTitle = $this->clean($title);

        if (! $cleanTitle) {
            return null;
        }

        return Str::of($cleanTitle)->replaceEnd(' - YouTube', '')->trim()->toString();
    }

    private function clean(?string $value): ?string
    {
        $clean = trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $clean !== '' ? $clean : null;
    }
}
