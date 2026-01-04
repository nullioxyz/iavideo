<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('username')
                    ->required(),
                TextInput::make('phone_number')
                    ->tel()
                    ->required(),
                DateTimePicker::make('phone_number_verified_at')
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Toggle::make('active')
                    ->required(),
                TextInput::make('credit_balance')
                    ->required()
                    ->numeric(),
                TextInput::make('invited_by_user_id')
                    ->numeric(),
                DateTimePicker::make('last_login_at'),
                DateTimePicker::make('suspended_at'),
                DateTimePicker::make('last_activity_at'),
                TextInput::make('user_agent'),
            ]);
    }
}
