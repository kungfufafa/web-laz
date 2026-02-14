<?php

namespace App\Filament\Resources\MemberPrayers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberPrayerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Doa Anggota')
                    ->description('Kelola konten doa serta visibilitasnya di aplikasi.')
                    ->columns(2)
                    ->components([
                        Select::make('user_id')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Status Tampil')
                            ->options([
                                'published' => 'Published',
                                'hidden' => 'Hidden',
                            ])
                            ->required()
                            ->default('published'),
                        Toggle::make('is_anonymous')
                            ->label('Tampilkan sebagai anonim')
                            ->required(),
                        Textarea::make('content')
                            ->label('Isi Doa')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
