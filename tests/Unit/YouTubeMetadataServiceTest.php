<?php

use App\Services\YouTubeMetadataService;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

test('can fetch youtube metadata from oembed and watch page', function () {
    Http::fake([
        'https://www.youtube.com/oembed*' => Http::response([
            'title' => 'Kajian Subuh - YouTube',
            'thumbnail_url' => 'https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
        ], 200),
        'https://www.youtube.com/watch*' => Http::response(
            '<html><head><meta property="og:description" content="Deskripsi kajian dari YouTube."></head></html>',
            200
        ),
    ]);

    $service = app(YouTubeMetadataService::class);
    $metadata = $service->fetch('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

    expect($metadata)->not()->toBeNull();
    expect($metadata['youtube_id'])->toBe('dQw4w9WgXcQ');
    expect($metadata['title'])->toBe('Kajian Subuh');
    expect($metadata['description'])->toBe('Deskripsi kajian dari YouTube.');
    expect($metadata['thumbnail'])->toBe('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg');
});

test('uses thumbnail fallback when metadata endpoint is unavailable', function () {
    Http::fake([
        'https://www.youtube.com/oembed*' => Http::response([], 500),
        'https://www.youtube.com/watch*' => Http::response('', 500),
    ]);

    $service = app(YouTubeMetadataService::class);
    $metadata = $service->fetch('https://youtu.be/dQw4w9WgXcQ');

    expect($metadata)->not()->toBeNull();
    expect($metadata['youtube_id'])->toBe('dQw4w9WgXcQ');
    expect($metadata['thumbnail'])->toBe('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg');
});
