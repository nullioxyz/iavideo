<?php

namespace App\Filament\Resources\Languages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LanguagesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('slug'),
                TextEntry::make('is_default'),
                TextEntry::make('active'),
                TextEntry::make('created_at'),
                TextEntry::make('updated_at'),
            ]);
    }
}

