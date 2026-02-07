<?php

namespace App\Filament\Resources\Inputs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InputForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('User')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('preset_id')
                    ->relationship('preset', 'name')
                    ->label('Preset')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('start_image_path')
                    ->label('Start Image Path')
                    ->required(),

                TextInput::make('original_filename')
                    ->label('Original Filename')
                    ->required(),

                TextInput::make('mime_type')
                    ->label('MIME Type')
                    ->required(),

                TextInput::make('size_bytes')
                    ->label('Size (bytes)')
                    ->required(),

                TextInput::make('credit_debited')
                    ->label('Credit/Debited')
                    ->required(),

                TextInput::make('status')
                    ->label('Status')
                    ->required(),

                DateTimePicker::make('created_at'),
                DateTimePicker::make('updated_at'),
            ]);
    }
}
