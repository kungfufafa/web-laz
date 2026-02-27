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
                Section::make(__('filament.resources.member_prayers.sections.main'))
                    ->description(__('filament.resources.member_prayers.descriptions.main'))
                    ->columns(2)
                    ->components([
                        Select::make('user_id')
                            ->label(__('filament.resources.member_prayers.fields.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label(__('filament.resources.member_prayers.fields.visibility_status'))
                            ->options([
                                'published' => __('filament.options.member_prayer_status.published'),
                                'hidden' => __('filament.options.member_prayer_status.hidden'),
                            ])
                            ->required()
                            ->default('published'),
                        Toggle::make('is_anonymous')
                            ->label(__('filament.resources.member_prayers.fields.is_anonymous'))
                            ->required(),
                        Textarea::make('content')
                            ->label(__('filament.resources.member_prayers.fields.content'))
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
