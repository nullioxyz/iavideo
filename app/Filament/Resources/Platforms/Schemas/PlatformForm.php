<?php

namespace App\Filament\Resources\Platforms\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PlatformForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                DateTimePicker::make('created_at'),
                DateTimePicker::make('updated_at'),
            ]);
    }
}
