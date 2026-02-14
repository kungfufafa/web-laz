<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

test('user can register', function () {
    $response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'access_token',
            'token_type',
        ]);

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

test('user can login', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
        ]);
});

test('authenticated user can get their profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = getJson('/api/user', ['Authorization' => 'Bearer '.$token]);

    $response->assertStatus(200)
        ->assertJson([
            'id' => $user->id,
            'email' => $user->email,
        ]);
});

test('authenticated user profile resolves avatar url from local disk path', function () {
    Storage::disk('local')->put('avatar-test.png', 'avatar-content');

    $user = User::factory()->create([
        'avatar_url' => 'avatar-test.png',
    ]);
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = getJson('/api/user', ['Authorization' => 'Bearer '.$token]);

    $response->assertStatus(200);
    expect($response->json('avatar_url'))->toEndWith('/api/media/avatar-test.png');
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = postJson('/api/logout', [], ['Authorization' => 'Bearer '.$token]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Logged out successfully']);

    expect($user->tokens()->count())->toBe(0);
});
