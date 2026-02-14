<?php

namespace App\Filament\Resources\Videos\Schemas;

use App\Services\YouTubeMetadataService;
use App\Support\YouTube;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Sumber YouTube')
                    ->description('Tempel link video untuk mengisi metadata otomatis.')
                    ->columns(2)
                    ->components([
                        TextInput::make('youtube_id')
                            ->label('Link YouTube')
                            ->placeholder('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
                            ->helperText('Judul, deskripsi, dan thumbnail akan terisi otomatis saat field ini selesai diinput.')
                            ->required()
                            ->live(onBlur: true)
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($set, ?string $state): void {
                                $metadata = app(YouTubeMetadataService::class)->fetch($state);

                                if (! $metadata) {
                                    return;
                                }

                                if (($state ?? '') !== $metadata['youtube_id']) {
                                    $set('youtube_id', $metadata['youtube_id']);
                                }

                                if (! empty($metadata['title'])) {
                                    $set('title', $metadata['title']);
                                }

                                if (! empty($metadata['description'])) {
                                    $set('description', $metadata['description']);
                                }

                                if (! empty($metadata['thumbnail'])) {
                                    $set('thumbnail', $metadata['thumbnail']);
                                }
                            })
                            ->dehydrateStateUsing(fn (?string $state): string => YouTube::extractVideoId($state) ?? trim((string) $state)),
                        TextInput::make('title')
                            ->label('Judul Video')
                            ->placeholder('Judul konten video')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('thumbnail')
                            ->label('Thumbnail URL')
                            ->url()
                            ->placeholder('https://...')
                            ->helperText('Bisa otomatis dari YouTube, atau isi manual.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Konten & Publikasi')
                    ->columns(2)
                    ->components([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(6)
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->label('Tampilkan di Aplikasi')
                            ->required(),
                    ]),
            ]);
    }
}
