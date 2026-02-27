<?php

namespace App\Filament\Resources\SocialNetworks;

use App\Domain\SocialNetworks\Models\SocialNetwork;
use App\Filament\Resources\SocialNetworks\Pages\CreateSocialNetworks;
use App\Filament\Resources\SocialNetworks\Pages\EditSocialNetworks;
use App\Filament\Resources\SocialNetworks\Pages\ListSocialNetworks;
use App\Filament\Resources\SocialNetworks\Pages\ViewSocialNetworks;
use App\Filament\Resources\SocialNetworks\Schemas\SocialNetworksForm;
use App\Filament\Resources\SocialNetworks\Schemas\SocialNetworksInfolist;
use App\Filament\Resources\SocialNetworks\Tables\SocialNetworksTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SocialNetworksResource extends Resource
{
    protected static ?string $model = SocialNetwork::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $navigationLabel = 'Social Networks';

    public static function form(Schema $schema): Schema
    {
        return SocialNetworksForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SocialNetworksInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SocialNetworksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialNetworks::route('/'),
            'create' => CreateSocialNetworks::route('/create'),
            'view' => ViewSocialNetworks::route('/{record}'),
            'edit' => EditSocialNetworks::route('/{record}/edit'),
        ];
    }
}

