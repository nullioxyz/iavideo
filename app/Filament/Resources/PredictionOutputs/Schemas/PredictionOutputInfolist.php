<?php

namespace App\Filament\Resources\PredictionOutputs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PredictionOutputInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('prediction.id')
                    ->label('Prediction'),
                TextEntry::make('kind')
                    ->badge(),
                TextEntry::make('path'),
                TextEntry::make('mime_type')
                    ->placeholder('-'),
                TextEntry::make('size_bytes')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
