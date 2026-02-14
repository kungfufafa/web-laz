<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('can list published articles', function () {
    Article::factory()->count(3)->create(['is_published' => true]);
    Article::factory()->create(['is_published' => false]);

    $response = $this->getJson('/api/articles');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('can show article detail', function () {
    $article = Article::factory()->create(['is_published' => true]);

    $response = $this->getJson("/api/articles/{$article->slug}");

    $response->assertStatus(200)
        ->assertJsonPath('data.title', $article->title);
});

test('article endpoint uses created_at when published_at is older than created_at', function () {
    $article = Article::factory()->create([
        'is_published' => true,
        'published_at' => now()->subYears(7),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->getJson('/api/articles');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.id', $article->id)
        ->assertJsonPath('data.0.published_at', $article->created_at->toISOString());
});

test('article endpoint returns absolute thumbnail url for uploaded file path', function () {
    $article = Article::factory()->create([
        'is_published' => true,
        'thumbnail' => 'articles/cover.jpg',
    ]);

    $response = $this->getJson('/api/articles');

    $response->assertStatus(200);
    $thumbnail = $response->json('data.0.thumbnail');

    expect($thumbnail)->toEndWith('/storage/articles/cover.jpg');
    expect($response->json('data.0.id'))->toBe($article->id);
});

test('article endpoint uses api media url when thumbnail exists in local disk', function () {
    Storage::disk('local')->put('legacy-cover.jpg', 'legacy-cover-content');

    Article::factory()->create([
        'is_published' => true,
        'thumbnail' => 'legacy-cover.jpg',
    ]);

    $response = $this->getJson('/api/articles');

    $response->assertStatus(200);
    expect($response->json('data.0.thumbnail'))->toEndWith('/api/media/legacy-cover.jpg');
});

test('can list published videos', function () {
    Video::factory()->count(2)->create(['is_published' => true]);
    Video::factory()->create(['is_published' => false]);

    $response = $this->getJson('/api/videos');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('video endpoint returns normalized youtube id when url is stored', function () {
    Video::factory()->create([
        'is_published' => true,
        'youtube_id' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);

    $response = $this->getJson('/api/videos');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.youtube_id', 'dQw4w9WgXcQ');
});

test('video endpoint returns absolute thumbnail url for uploaded file path', function () {
    Video::factory()->create([
        'is_published' => true,
        'thumbnail' => 'videos/cover.jpg',
    ]);

    $response = $this->getJson('/api/videos');

    $response->assertStatus(200);
    $thumbnail = $response->json('data.0.thumbnail');

    expect($thumbnail)->toEndWith('/storage/videos/cover.jpg');
});

test('media endpoint can serve file from local disk', function () {
    Storage::disk('local')->put('legacy-image.jpg', 'legacy-image-content');

    $response = $this->get('/api/media/legacy-image.jpg');

    $response->assertOk();
});
