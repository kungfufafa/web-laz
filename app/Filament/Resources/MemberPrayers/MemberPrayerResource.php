<?php

namespace App\Filament\Resources\MemberPrayers;

use App\Filament\Resources\MemberPrayers\Pages\CreateMemberPrayer;
use App\Filament\Resources\MemberPrayers\Pages\EditMemberPrayer;
use App\Filament\Resources\MemberPrayers\Pages\ListMemberPrayers;
use App\Filament\Resources\MemberPrayers\Schemas\MemberPrayerForm;
use App\Filament\Resources\MemberPrayers\Tables\MemberPrayersTable;
use App\Models\MemberPrayer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class MemberPrayerResource extends Resource
{
    protected static ?string $model = MemberPrayer::class;

    protected static ?string $modelLabel = 'Doa Anggota';

    protected static ?string $navigationLabel = 'Doa Anggota';

    protected static string|UnitEnum|null $navigationGroup = 'Konten';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hand-raised';

    public static function form(Schema $schema): Schema
    {
        return MemberPrayerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemberPrayersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberPrayers::route('/'),
            'create' => CreateMemberPrayer::route('/create'),
            'edit' => EditMemberPrayer::route('/{record}/edit'),
        ];
    }
}
