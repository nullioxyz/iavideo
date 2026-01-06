<?php

namespace App\Filament\Resources\Predictions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PredictionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('input_id')
                    ->relationship('input', 'id')
                    ->required(),
                Select::make('model_id')
                    ->relationship('model', 'name')
                    ->required(),
                TextInput::make('external_id'),
                Select::make('status')
                    ->options([
            'queued' => 'Queued',
            'starting' => 'Starting',
            'submitting' => 'Submitting',
            'processing' => 'Processing',
            'succeeded' => 'Succeeded',
            'failed' => 'Failed',
            'canceled' => 'Canceled',
            'refunded' => 'Refunded',
        ])
                    ->default('queued')
                    ->required(),
                Select::make('source')
                    ->options(['web' => 'Web', 'admin' => 'Admin', 'api' => 'Api'])
                    ->default('web')
                    ->required(),
                TextInput::make('attempt')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('retry_of_prediction_id')
                    ->numeric(),
                DateTimePicker::make('queued_at'),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('finished_at'),
                DateTimePicker::make('failed_at'),
                TextInput::make('duration_seconds')
                    ->numeric(),
                TextInput::make('processing_ms')
                    ->numeric(),
                TextInput::make('total_ms')
                    ->numeric(),
                TextInput::make('cost_estimate_usd')
                    ->numeric(),
                TextInput::make('cost_actual_usd')
                    ->numeric(),
                TextInput::make('error_code'),
                Textarea::make('error_message')
                    ->columnSpanFull(),
                TextInput::make('request_payload'),
                TextInput::make('response_payload'),
            ]);
    }
}
