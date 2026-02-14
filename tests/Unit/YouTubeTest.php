<?php

use App\Support\YouTube;

test('can extract youtube video id from common formats', function () {
    expect(YouTube::extractVideoId('dQw4w9WgXcQ'))->toBe('dQw4w9WgXcQ');
    expect(YouTube::extractVideoId('https://www.youtube.com/watch?v=dQw4w9WgXcQ'))->toBe('dQw4w9WgXcQ');
    expect(YouTube::extractVideoId('https://youtu.be/dQw4w9WgXcQ'))->toBe('dQw4w9WgXcQ');
    expect(YouTube::extractVideoId('https://www.youtube.com/shorts/dQw4w9WgXcQ'))->toBe('dQw4w9WgXcQ');
});

test('returns null for unsupported youtube input', function () {
    expect(YouTube::extractVideoId('https://example.com/video'))->toBeNull();
    expect(YouTube::extractVideoId(''))->toBeNull();
});
