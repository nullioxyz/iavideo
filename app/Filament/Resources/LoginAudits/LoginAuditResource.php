<?php

namespace App\Filament\Resources\LoginAudits;

use App\Domain\Auth\Models\LoginAudit;
use App\Filament\Resources\LoginAudits\Pages\ListLoginAudits;
use App\Filament\Resources\LoginAudits\Pages\ViewLoginAudit;
use App\Filament\Resources\LoginAudits\Schemas\LoginAuditInfolist;
use App\Filament\Resources\LoginAudits\Tables\LoginAuditsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LoginAuditResource extends Resource
{
    protected static ?string $model = LoginAudit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Security';

    public static function infolist(Schema $schema): Schema
    {
        return LoginAuditInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoginAuditsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoginAudits::route('/'),
            'view' => ViewLoginAudit::route('/{record}'),
        ];
    }
}

