<?php

namespace App\Filament\Resources\Presets;

use App\Domain\AIModels\Models\Preset;
use App\Filament\Resources\Presets\Pages\CreatePresets;
use App\Filament\Resources\Presets\Pages\EditPresets;
use App\Filament\Resources\Presets\Pages\ListPresets;
use App\Filament\Resources\Presets\Pages\ViewPresets;
use App\Filament\Resources\Presets\Schemas\PresetsForm;
use App\Filament\Resources\Presets\Schemas\PresetsInfolist;
use App\Filament\Resources\Presets\Tables\PresetsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PresetsResource extends Resource
{
    protected static ?string $model = Preset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PresetsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PresetsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PresetsTable::configure($table);
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
            'index' => ListPresets::route('/'),
            'create' => CreatePresets::route('/create'),
            'view' => ViewPresets::route('/{record}'),
            'edit' => EditPresets::route('/{record}/edit'),
        ];
    }
}
