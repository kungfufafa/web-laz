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
                Section::make(__('filament.resources.videos.sections.youtube_source'))
                    ->description(__('filament.resources.videos.descriptions.youtube_source'))
                    ->columns(2)
                    ->components([
                        TextInput::make('youtube_id')
                            ->label(__('filament.resources.videos.fields.youtube_link'))
                            ->placeholder(__('filament.resources.videos.placeholders.youtube_link'))
                            ->helperText(__('filament.resources.videos.helper_text.youtube_link'))
                            ->required()
                            ->rule(static function (): \Closure {
                                return static function (string $attribute, mixed $value, \Closure $fail): void {
                                    $input = is_scalar($value) ? trim((string) $value) : '';

                                    if (YouTube::extractVideoId($input) === null) {
                                        $fail(__('filament.notifications.invalid_youtube_link'));
                                    }
                                };
                            })
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
                            ->dehydrateStateUsing(fn (?string $state): ?string => YouTube::extractVideoId($state)),
                        TextInput::make('title')
                            ->label(__('filament.resources.videos.fields.title'))
                            ->placeholder(__('filament.resources.videos.placeholders.title'))
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('thumbnail')
                            ->label(__('filament.resources.videos.fields.thumbnail_url'))
                            ->url()
                            ->placeholder(__('filament.resources.videos.placeholders.thumbnail_url'))
                            ->helperText(__('filament.resources.videos.helper_text.thumbnail_url'))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('filament.resources.videos.sections.content_and_publication'))
                    ->columns(2)
                    ->components([
                        Textarea::make('description')
                            ->label(__('filament.resources.videos.fields.description'))
                            ->rows(6)
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->label(__('filament.resources.videos.fields.is_published'))
                            ->required(),
                    ]),
            ]);
    }
}
