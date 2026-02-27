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
                Section::make('Informasi Artikel')
                    ->description('Metadata artikel untuk URL dan tampilan list konten.')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->label('Judul')
                            ->placeholder('Masukkan judul artikel')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->placeholder('slug-artikel')
                            ->required()
                            ->unique(ignoreRecord: true),
                        FileUpload::make('thumbnail')
                            ->label('Thumbnail')
                            ->image()
                            ->disk('public')
                            ->directory('articles'),
                        Toggle::make('is_published')
                            ->label('Publikasikan Artikel')
                            ->required(),
                    ]),
                Section::make('Isi Artikel')
                    ->components([
                        RichEditor::make('content')
                            ->label('Konten')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
