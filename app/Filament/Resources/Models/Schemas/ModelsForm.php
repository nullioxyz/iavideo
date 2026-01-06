<?php

namespace App\Filament\Resources\Models\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ModelsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('platform_id')
                ->relationship('platform', 'name')
                ->searchable()
                ->preload()
                ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                TextInput::make('version')
                    ->label('Version')
                    ->required(),

                Toggle::make('active')
                    ->required(),

                DateTimePicker::make('created_at'),
                DateTimePicker::make('updated_at'),
            ]);
    }
}
