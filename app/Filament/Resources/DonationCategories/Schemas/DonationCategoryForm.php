<?php

namespace App\Filament\Resources\DonationCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DonationCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('label')
                    ->label(__('filament.resources.donation_categories.fields.label'))
                    ->placeholder(__('filament.resources.donation_categories.placeholders.label'))
                    ->helperText(__('filament.resources.donation_categories.helper_text.label'))
                    ->required()
                    ->maxLength(100)
                    ->columnSpan(2),
                Toggle::make('is_active')
                    ->label(__('filament.resources.donation_categories.fields.is_active'))
                    ->default(true)
                    ->columnSpan(2),
                Textarea::make('description')
                    ->label(__('filament.resources.donation_categories.fields.description'))
                    ->rows(3)
                    ->maxLength(255)
                    ->columnSpan(2),
            ]);
    }
}
