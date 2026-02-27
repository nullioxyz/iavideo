<?php

namespace App\Filament\Resources\Institutionals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InstitutionalsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('title'),
            TextEntry::make('slug'),
            TextEntry::make('subtitle'),
            TextEntry::make('short_description'),
            TextEntry::make('description'),
            TextEntry::make('active'),
            TextEntry::make('created_at'),
            TextEntry::make('updated_at'),
        ]);
    }
}

