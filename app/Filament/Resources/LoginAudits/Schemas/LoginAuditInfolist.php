<?php

namespace App\Filament\Resources\LoginAudits\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LoginAuditInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id'),
                IconEntry::make('success')
                    ->boolean(),
                TextEntry::make('failure_reason')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('user.email')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Login Email')
                    ->placeholder('-'),
                TextEntry::make('ip_address')
                    ->placeholder('-'),
                TextEntry::make('forwarded_for')
                    ->placeholder('-'),
                TextEntry::make('country_code')
                    ->label('Country')
                    ->placeholder('-'),
                TextEntry::make('region')
                    ->placeholder('-'),
                TextEntry::make('city')
                    ->placeholder('-'),
                TextEntry::make('browser')
                    ->placeholder('-'),
                TextEntry::make('platform')
                    ->placeholder('-'),
                TextEntry::make('user_agent')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}

