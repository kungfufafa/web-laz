<?php

namespace Tests\Feature\Api;

use App\Models\MemberPrayer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('can list prayers', function () {
    MemberPrayer::factory()->count(5)->create();

    $response = $this->getJson('/api/prayers');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
});

test('can store prayer', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/prayers', [
        'content' => 'My test prayer',
        'is_anonymous' => true,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.content', 'My test prayer')
        ->assertJsonPath('data.user_name', 'Hamba Allah');
});

test('can toggle amen', function () {
    $user = User::factory()->create();
    $prayer = MemberPrayer::factory()->create(['likes_count' => 0]);
    Sanctum::actingAs($user);

    // Toggle on
    $response = $this->postJson("/api/prayers/{$prayer->id}/amen");
    $response->assertStatus(200)
        ->assertJsonPath('liked', true)
        ->assertJsonPath('likes_count', 1);

    // Toggle off
    $response = $this->postJson("/api/prayers/{$prayer->id}/amen");
    $response->assertStatus(200)
        ->assertJsonPath('liked', false)
        ->assertJsonPath('likes_count', 0);
});
