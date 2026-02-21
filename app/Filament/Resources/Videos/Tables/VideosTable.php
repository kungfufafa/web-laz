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
                    ->searchable(),
                TextColumn::make('youtube_id')
                    ->label('Link YouTube')
                    ->formatStateUsing(fn (?string $state): string => $state ? "https://youtu.be/{$state}" : '-')
                    ->url(fn ($record): ?string => $record->youtube_id ? "https://www.youtube.com/watch?v={$record->youtube_id}" : null)
                    ->openUrlInNewTab()
                    ->searchable(),
                IconColumn::make('is_published')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Publikasi')
                    ->trueLabel('Published')
                    ->falseLabel('Draft'),
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
