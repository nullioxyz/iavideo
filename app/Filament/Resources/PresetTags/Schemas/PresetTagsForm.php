<?php

namespace App\Filament\Resources\PresetTags\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PresetTagsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Optional. If empty, generated from name.')
                    ->nullable(),

                Toggle::make('active')
                    ->default(true)
                    ->required(),

                DateTimePicker::make('created_at'),
                DateTimePicker::make('updated_at'),
            ]);
    }
}

