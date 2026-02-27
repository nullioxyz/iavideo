<?php

namespace App\Filament\Resources\SocialNetworks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SocialNetworksInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('url'),
            TextEntry::make('slug'),
            TextEntry::make('active'),
        ]);
    }
}

