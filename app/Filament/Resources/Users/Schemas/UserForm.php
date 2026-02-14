<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Akun')
                    ->description('Data utama akun pengguna untuk autentikasi dan otorisasi.')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Contoh: Ahmad Fauzi')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->placeholder('contoh@email.com')
                            ->required(),
                        TextInput::make('password')
                            ->label('Password Awal')
                            ->password()
                            ->placeholder('Minimal 8 karakter')
                            ->required()
                            ->visibleOn('create'),
                        Select::make('role')
                            ->label('Peran')
                            ->options([
                                'admin' => 'Admin',
                                'member' => 'Member',
                            ])
                            ->required()
                            ->default('member'),
                    ]),
                Section::make('Profil')
                    ->description('Informasi tambahan yang ditampilkan di aplikasi.')
                    ->columns(2)
                    ->components([
                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->placeholder('08xxxxxxxxxx'),
                        FileUpload::make('avatar_url')
                            ->label('Foto Profil')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars'),
                    ]),
            ]);
    }
}
