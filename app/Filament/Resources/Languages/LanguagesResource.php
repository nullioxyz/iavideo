<?php

namespace App\Filament\Resources\Languages;

use App\Domain\Languages\Models\Language;
use App\Filament\Resources\Languages\Pages\CreateLanguages;
use App\Filament\Resources\Languages\Pages\EditLanguages;
use App\Filament\Resources\Languages\Pages\ListLanguages;
use App\Filament\Resources\Languages\Pages\ViewLanguages;
use App\Filament\Resources\Languages\Schemas\LanguagesForm;
use App\Filament\Resources\Languages\Schemas\LanguagesInfolist;
use App\Filament\Resources\Languages\Tables\LanguagesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LanguagesResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Languages';

    public static function form(Schema $schema): Schema
    {
        return LanguagesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LanguagesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LanguagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLanguages::route('/'),
            'create' => CreateLanguages::route('/create'),
            'view' => ViewLanguages::route('/{record}'),
            'edit' => EditLanguages::route('/{record}/edit'),
        ];
    }
}
