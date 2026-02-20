<?php

use App\Models\User;

use function Pest\Laravel\postJson;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('account deletion succeeds with valid phone', function () {
    $user = User::factory()->create([
        'phone' => '081234567890',
    ]);

    $response = postJson('/api/account-deletion', [
        'phone' => '081234567890',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Akun berhasil dihapus',
        ]);

    $this->assertSoftDeleted('users', [
        'id' => $user->id,
    ]);
});

test('account deletion fails with invalid phone format', function () {
    $response = postJson('/api/account-deletion', [
        'phone' => '123456789',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

test('account deletion fails when phone not found', function () {
    $response = postJson('/api/account-deletion', [
        'phone' => '089876543210',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Akun dengan nomor telepon tersebut tidak ditemukan',
        ]);
});

test('account deletion works with +62 prefix', function () {
    $user = User::factory()->create([
        'phone' => '081234567890',
    ]);

    $response = postJson('/api/account-deletion', [
        'phone' => '+6281234567890',
    ]);

    $response->assertStatus(200);
    $this->assertSoftDeleted('users', [
        'id' => $user->id,
    ]);
});

test('account deletion works with 62 prefix', function () {
    $user = User::factory()->create([
        'phone' => '081234567890',
    ]);

    $response = postJson('/api/account-deletion', [
        'phone' => '6281234567890',
    ]);

    $response->assertStatus(200);
    $this->assertSoftDeleted('users', [
        'id' => $user->id,
    ]);
});

test('account deletion requires phone field', function () {
    $response = postJson('/api/account-deletion', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

test('account deletion is rate limited', function () {
    $response = postJson('/api/account-deletion', [
        'phone' => '081234567890',
    ]);

    $response->assertStatus(404); // User not found

    // Try 5 more times quickly (exceeds rate limit of 5 per minute)
    for ($i = 0; $i < 5; $i++) {
        postJson('/api/account-deletion', [
            'phone' => '081234567890',
        ]);
    }

    // Next request should be rate limited
    $response = postJson('/api/account-deletion', [
        'phone' => '081234567890',
    ]);

    $response->assertStatus(429);
});
