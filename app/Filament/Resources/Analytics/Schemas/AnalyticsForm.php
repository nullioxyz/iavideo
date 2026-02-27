<?php

namespace App\Filament\Resources\Analytics\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AnalyticsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            TextInput::make('slug')->required(),
            TextInput::make('provider'),
            TextInput::make('tracking_id'),
            Textarea::make('script_head')->rows(6),
            Textarea::make('script_body')->rows(6),
            Toggle::make('active')->default(true),
        ]);
    }
}

