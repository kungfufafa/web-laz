<?php

use App\Filament\Resources\Videos\Pages\CreateVideo;
use App\Models\User;
use App\Models\Video;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('youtube link must be valid when creating video from filament form', function (): void {
    Filament::setCurrentPanel('admin');

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    Livewire::test(CreateVideo::class)
        ->fillForm([
            'youtube_id' => 'not-a-youtube-link',
            'title' => 'Video Test',
            'description' => 'Deskripsi',
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['youtube_id']);

    expect(Video::query()->count())->toBe(0);
});
