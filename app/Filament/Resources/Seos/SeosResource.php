<?php

namespace App\Filament\Resources\Seos;

use App\Domain\Seo\Models\Seo;
use App\Filament\Resources\Seos\Pages\CreateSeos;
use App\Filament\Resources\Seos\Pages\EditSeos;
use App\Filament\Resources\Seos\Pages\ListSeos;
use App\Filament\Resources\Seos\Pages\ViewSeos;
use App\Filament\Resources\Seos\Schemas\SeosForm;
use App\Filament\Resources\Seos\Schemas\SeosInfolist;
use App\Filament\Resources\Seos\Tables\SeosTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeosResource extends Resource
{
    protected static ?string $model = Seo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel = 'SEO';

    public static function form(Schema $schema): Schema
    {
        return SeosForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SeosInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeos::route('/'),
            'create' => CreateSeos::route('/create'),
            'view' => ViewSeos::route('/{record}'),
            'edit' => EditSeos::route('/{record}/edit'),
        ];
    }
}

