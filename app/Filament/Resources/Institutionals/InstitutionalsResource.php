<?php

namespace App\Filament\Resources\Institutionals;

use App\Domain\Institutional\Models\Institutional;
use App\Filament\Resources\Institutionals\Pages\CreateInstitutionals;
use App\Filament\Resources\Institutionals\Pages\EditInstitutionals;
use App\Filament\Resources\Institutionals\Pages\ListInstitutionals;
use App\Filament\Resources\Institutionals\Pages\ViewInstitutionals;
use App\Filament\Resources\Institutionals\Schemas\InstitutionalsForm;
use App\Filament\Resources\Institutionals\Schemas\InstitutionalsInfolist;
use App\Filament\Resources\Institutionals\Tables\InstitutionalsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InstitutionalsResource extends Resource
{
    protected static ?string $model = Institutional::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Institutionals';

    public static function form(Schema $schema): Schema
    {
        return InstitutionalsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstitutionalsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstitutionalsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstitutionals::route('/'),
            'create' => CreateInstitutionals::route('/create'),
            'view' => ViewInstitutionals::route('/{record}'),
            'edit' => EditInstitutionals::route('/{record}/edit'),
        ];
    }
}

