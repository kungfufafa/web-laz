<?php

namespace App\Filament\Resources\Articles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament.resources.articles.fields.title'))
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label(__('filament.resources.articles.fields.is_published'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('filament.resources.articles.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label(__('filament.resources.articles.filters.publication'))
                    ->trueLabel(__('filament.resources.articles.filters.published'))
                    ->falseLabel(__('filament.resources.articles.filters.draft')),
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
