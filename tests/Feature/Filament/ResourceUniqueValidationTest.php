<?php

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Article;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

test('email must be unique when creating user from filament form', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    User::factory()->create([
        'email' => 'duplicate@example.test',
    ]);

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'User Baru',
            'email' => 'duplicate@example.test',
            'password' => 'password',
            'role' => 'member',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('slug must be unique when creating article from filament form', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    Article::factory()->create([
        'slug' => 'slug-sudah-ada',
    ]);

    $this->actingAs($admin);

    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => 'Judul Baru',
            'slug' => 'slug-sudah-ada',
            'content' => '<p>Konten artikel</p>',
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

test('phone must be unique when creating user from filament form', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    User::factory()->create([
        'phone' => '081234567890',
    ]);

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'User Baru 2',
            'email' => 'user-baru-2@example.test',
            'password' => 'password',
            'role' => 'member',
            'phone' => '081234567890',
        ])
        ->call('create')
        ->assertHasFormErrors(['phone' => 'unique']);
});

test('editing user can keep the same email and phone', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $user = User::factory()->create([
        'email' => 'same@example.test',
        'phone' => '081234567890',
        'role' => 'admin',
    ]);
    $originalPasswordHash = $user->password;

    $this->actingAs($admin);

    Livewire::test(EditUser::class, [
        'record' => $user->getKey(),
    ])
        ->fillForm([
            'name' => 'Nama Diperbarui',
            'email' => 'same@example.test',
            'phone' => '081234567890',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->password)->toBe($originalPasswordHash);
});

test('admin can reset user password from edit user form', function (): void {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $user = User::factory()->create([
        'role' => 'member',
    ]);

    $this->actingAs($admin);

    Livewire::test(EditUser::class, [
        'record' => $user->getKey(),
    ])
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'member',
            'password' => 'new-password-123',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect(Hash::check('new-password-123', $user->password))->toBeTrue();
});
