<?php

namespace App\Filament\Resources\Seos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SeosInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('slug'),
            TextEntry::make('meta_title'),
            TextEntry::make('meta_description'),
            TextEntry::make('meta_keywords'),
            TextEntry::make('canonical_url'),
            TextEntry::make('og_title'),
            TextEntry::make('og_description'),
            TextEntry::make('twitter_title'),
            TextEntry::make('twitter_description'),
            TextEntry::make('active'),
        ]);
    }
}

