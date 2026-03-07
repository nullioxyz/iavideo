<?php

namespace App\Filament\Resources\Presets\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PresetsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),

                TextEntry::make('aspect_ratio')
                    ->label('Aspect Ratio'),

                TextEntry::make('tags')
                    ->label('Tags')
                    ->state(fn ($record) => $record->tags->pluck('name')->implode(', ')),

                TextEntry::make('duration_seconds')
                    ->label('Duration (seconds)'),

                TextEntry::make('cost_estimate_usd')
                    ->label('Legacy Cost Estimate (deprecated)'),

                TextEntry::make('preview_image_url')
                    ->label('Preview Image URL')
                    ->state(fn ($record) => $record->previewImageUrl()),

                ImageEntry::make('preview_image_media')
                    ->label('Preview Image')
                    ->state(fn ($record) => $record->previewImageUrl()),

                TextEntry::make('preview_video_url')
                    ->label('Preview Video URL')
                    ->state(fn ($record) => $record->previewVideoUrl()),

                TextEntry::make('preview_video_player')
                    ->label('Preview Video')
                    ->state(fn ($record) => $record->previewVideoUrl())
                    ->formatStateUsing(function ($state): HtmlString|string {
                        if (! is_string($state) || $state === '') {
                            return 'No video uploaded';
                        }

                        return new HtmlString('<video controls style="max-width:360px;border-radius:8px;" src="'.$state.'"></video>');
                    })
                    ->html(),

                TextEntry::make('active'),

                TextEntry::make('created_at'),
                TextEntry::make('updated_at'),
            ]);
    }
}
