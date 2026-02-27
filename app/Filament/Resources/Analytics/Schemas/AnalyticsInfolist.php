<?php

namespace App\Filament\Resources\Analytics\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AnalyticsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('slug'),
            TextEntry::make('provider'),
            TextEntry::make('tracking_id'),
            TextEntry::make('script_head'),
            TextEntry::make('script_body'),
            TextEntry::make('active'),
        ]);
    }
}

