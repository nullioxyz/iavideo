<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('username'),
                TextEntry::make('phone_number'),
                TextEntry::make('phone_number_verified_at')
                    ->dateTime(),
                IconEntry::make('active')
                    ->boolean(),
                TextEntry::make('credit_balance')
                    ->numeric(),
                TextEntry::make('invited_by_user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('last_login_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('suspended_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_activity_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('user_agent')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
