<?php

namespace App\Filament\Resources\PredictionOutputs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PredictionOutputForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('prediction_id')
                    ->relationship('prediction', 'id')
                    ->required(),
                Select::make('kind')
                    ->options(['video' => 'Video', 'thumbnail' => 'Thumbnail', 'gif' => 'Gif'])
                    ->default('video')
                    ->required(),
                TextInput::make('path')
                    ->required(),
                TextInput::make('mime_type'),
                TextInput::make('size_bytes')
                    ->numeric(),
            ]);
    }
}
