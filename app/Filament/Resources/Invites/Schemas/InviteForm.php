<?php

namespace App\Filament\Resources\Invites\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InviteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('token')
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated(fn (string $operation): bool => $operation !== 'create')
                    ->visible(fn (string $operation): bool => $operation !== 'create'),
                TextInput::make('credits_granted')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                Select::make('invited_by_user_id')
                    ->label('Invited by')
                    ->relationship('invitedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DateTimePicker::make('used_at'),
                DateTimePicker::make('expires_at'),
            ]);
    }
}
