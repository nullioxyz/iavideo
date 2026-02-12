<?php

namespace App\Filament\Resources\Invites\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InviteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('email'),
                TextEntry::make('token'),
                TextEntry::make('share_url')
                    ->label('Share link')
                    ->copyable()
                    ->url(fn ($record) => $record->share_url)
                    ->openUrlInNewTab(),
                TextEntry::make('copy_share_link')
                    ->label('Copy link')
                    ->state('Copy')
                    ->badge()
                    ->copyable()
                    ->copyableState(fn ($record) => $record->share_url),
                TextEntry::make('credits_granted')
                    ->numeric(),
                TextEntry::make('invitedBy.name')
                    ->label('Invited by')
                    ->placeholder('-'),
                TextEntry::make('used_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('expires_at')
                    ->dateTime()
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
