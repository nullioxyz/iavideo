<?php

namespace App\Filament\Resources\Models\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ModelsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug')
                    ->label('Slug'),
                TextEntry::make('version')
                    ->label('Version'),

                IconEntry::make('active')
                    ->boolean(),

                TextEntry::make('created_at'),
                TextEntry::make('updated_at'),
            ]);
    }
}
