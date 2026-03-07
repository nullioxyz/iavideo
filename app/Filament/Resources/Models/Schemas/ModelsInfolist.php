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
                TextEntry::make('provider_model_key')
                    ->label('Provider / Model Key'),
                TextEntry::make('version')
                    ->label('Version'),
                TextEntry::make('cost_per_second_usd')
                    ->label('Cost / Second (USD)'),
                TextEntry::make('sort_order')
                    ->label('Sort Order'),

                IconEntry::make('active')
                    ->boolean(),
                IconEntry::make('public_visible')
                    ->boolean(),

                TextEntry::make('created_at'),
                TextEntry::make('updated_at'),
            ]);
    }
}
