<?php

namespace App\Filament\Resources\SocialNetworks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SocialNetworksForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('url')->url()->required(),
            TextInput::make('slug')->required(),
            Toggle::make('active')->default(true),
        ]);
    }
}

