<?php

namespace App\Filament\Resources\Presets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PresetsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),

                TextEntry::make('aspect_ratio')
                    ->label('Aspect Ratio'),

                TextEntry::make('duration_seconds')
                    ->label('Duration (seconds)'),

                TextEntry::make('cost_estimate_usd')
                    ->label('Cost Estimate (USD)'),

                TextEntry::make('preview_video_url')
                    ->label('Preview Video URL'),

                TextEntry::make('active'),

                TextEntry::make('created_at'),
                TextEntry::make('updated_at'),
            ]);
    }
}
