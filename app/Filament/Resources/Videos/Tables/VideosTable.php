<?php

namespace App\Filament\Resources\Videos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament.resources.videos.fields.title'))
                    ->searchable(),
                TextColumn::make('youtube_id')
                    ->label(__('filament.resources.videos.fields.youtube_link'))
                    ->formatStateUsing(fn (?string $state): string => $state ? "https://youtu.be/{$state}" : '-')
                    ->url(fn ($record): ?string => $record->youtube_id ? "https://www.youtube.com/watch?v={$record->youtube_id}" : null)
                    ->openUrlInNewTab()
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label(__('filament.resources.videos.fields.is_published'))
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label(__('filament.resources.videos.filters.publication'))
                    ->trueLabel(__('filament.resources.videos.filters.published'))
                    ->falseLabel(__('filament.resources.videos.filters.draft')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
