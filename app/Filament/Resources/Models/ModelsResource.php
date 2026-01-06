<?php

namespace App\Filament\Resources\Models;

use App\Domain\AIModels\Models\Model;
use App\Filament\Resources\Models\Pages\CreateModels;
use App\Filament\Resources\Models\Pages\EditModels;
use App\Filament\Resources\Models\Pages\ListModels;
use App\Filament\Resources\Models\Pages\ViewModels;
use App\Filament\Resources\Models\Schemas\ModelsForm;
use App\Filament\Resources\Models\Schemas\ModelsInfolist;
use App\Filament\Resources\Models\Tables\ModelsTable;
use App\Models\Models;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ModelsResource extends Resource
{
    protected static ?string $model = Model::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ModelsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ModelsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModelsTable::configure($table);
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
            'index' => ListModels::route('/'),
            'create' => CreateModels::route('/create'),
            'view' => ViewModels::route('/{record}'),
            'edit' => EditModels::route('/{record}/edit'),
        ];
    }
}
