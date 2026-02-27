<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('email'),
            TextEntry::make('phone'),
            TextEntry::make('message'),
            TextEntry::make('is_user'),
            TextEntry::make('created_at')->dateTime(),
        ]);
    }
}
