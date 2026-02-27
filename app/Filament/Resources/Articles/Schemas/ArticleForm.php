<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('filament.resources.articles.sections.information'))
                    ->description(__('filament.resources.articles.descriptions.information'))
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->label(__('filament.resources.articles.fields.title'))
                            ->placeholder(__('filament.resources.articles.placeholders.title'))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->label(__('filament.resources.articles.fields.slug'))
                            ->placeholder(__('filament.resources.articles.placeholders.slug'))
                            ->required()
                            ->unique(ignoreRecord: true),
                        FileUpload::make('thumbnail')
                            ->label(__('filament.resources.articles.fields.thumbnail'))
                            ->image()
                            ->disk('public')
                            ->directory('articles'),
                        Toggle::make('is_published')
                            ->label(__('filament.resources.articles.fields.is_published'))
                            ->required(),
                    ]),
                Section::make(__('filament.resources.articles.sections.content'))
                    ->components([
                        RichEditor::make('content')
                            ->label(__('filament.resources.articles.fields.content'))
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
