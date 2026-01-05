<?php

namespace App\Filament\Resources\Predictions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PredictionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('input.id')
                    ->label('Input'),
                TextEntry::make('model.name')
                    ->label('Model'),
                TextEntry::make('external_id')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('source')
                    ->badge(),
                TextEntry::make('attempt')
                    ->numeric(),
                TextEntry::make('retry_of_prediction_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('queued_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('started_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('finished_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('failed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('duration_seconds')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('processing_ms')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('total_ms')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('cost_estimate_usd')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('cost_actual_usd')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('error_code')
                    ->placeholder('-'),
                TextEntry::make('error_message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
