<?php

namespace App\Filament\Resources\PresetTags\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PresetTagsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug')
                    ->label('Slug'),
                TextEntry::make('presets_count')
                    ->label('Presets')
                    ->state(fn ($record) => $record->presets()->count()),
                IconEntry::make('active')
                    ->boolean(),
                TextEntry::make('created_at'),
                TextEntry::make('updated_at'),
            ]);
    }
}

