<?php

namespace App\Filament\Resources\Presets\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;

class PresetsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                Select::make('default_model_id')
                    ->relationship('model', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                TextInput::make('name')
                    ->required(),
                
                Textarea::make('prompt')
                    ->label('Prompt')
                    ->required(),

                Textarea::make('negative_prompt')
                    ->label('Negative prompt')
                    ->required(),
        
                TextInput::make('aspect_ratio')
                    ->label('Aspect Ratio')
                    ->required(),

                TextInput::make('duration_seconds')
                    ->label('Duration (seconds)')
                    ->required(),

                TextInput::make('cost_estimate_usd')
                    ->label('Cost Estimate (USD)')
                    ->required(),
                TextInput::make('preview_video_url')
                    ->label('Preview Video URL')
                    ->required(),

                DateTimePicker::make('created_at'),
                DateTimePicker::make('updated_at'),

                Toggle::make('active')
                    ->required(),
            ]);
    }
}
