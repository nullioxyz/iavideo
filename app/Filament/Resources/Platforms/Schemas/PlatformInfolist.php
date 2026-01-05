<?php

namespace App\Filament\Resources\Platforms\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PlatformInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Name'),
                TextEntry::make('slug')
                    ->label('Slug')
            ]);
    }
}
