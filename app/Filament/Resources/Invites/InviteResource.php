<?php

namespace App\Filament\Resources\Invites;

use App\Domain\Invites\Models\Invite;
use App\Filament\Resources\Invites\Pages\CreateInvite;
use App\Filament\Resources\Invites\Pages\EditInvite;
use App\Filament\Resources\Invites\Pages\ListInvites;
use App\Filament\Resources\Invites\Pages\ViewInvite;
use App\Filament\Resources\Invites\Schemas\InviteForm;
use App\Filament\Resources\Invites\Schemas\InviteInfolist;
use App\Filament\Resources\Invites\Tables\InvitesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InviteResource extends Resource
{
    protected static ?string $model = Invite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Invites';

    protected static ?string $modelLabel = 'Invite';

    protected static ?string $pluralModelLabel = 'Invites';

    public static function form(Schema $schema): Schema
    {
        return InviteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InviteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvitesTable::configure($table);
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
            'index' => ListInvites::route('/'),
            'create' => CreateInvite::route('/create'),
            'view' => ViewInvite::route('/{record}'),
            'edit' => EditInvite::route('/{record}/edit'),
        ];
    }
}
