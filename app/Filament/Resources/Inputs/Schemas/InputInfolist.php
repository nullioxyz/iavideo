<?php

namespace App\Filament\Resources\Inputs\Schemas;

use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\PredictionOutput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InputInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('preset.name')
                    ->label('Preset'),

                TextEntry::make('user.name')
                    ->label('User'),

                TextEntry::make('user.name')
                    ->label('User'),

                ImageEntry::make('start_image')
                    ->state(fn (Input $record) => $record->getFirstMediaUrl('start_image'))
                    ->label('Start image'),

                TextEntry::make('start_image_path')
                    ->label('Start Image Path'),

                TextEntry::make('original_filename')
                    ->label('Original Filename'),

                TextEntry::make('title')
                    ->label('Input Name')
                    ->placeholder('-'),

                TextEntry::make('mime_type')
                    ->label('MIME Type'),

                TextEntry::make('size_bytes')
                    ->label('Size (bytes)'),

                TextEntry::make('credit_debited')
                    ->label('Credit/Debited'),

                TextEntry::make('status')
                    ->label('Status'),

                TextEntry::make('prediction.status')
                    ->label('Prediction Status')
                    ->placeholder('-'),

                TextEntry::make('prediction.external_id')
                    ->label('Prediction External ID')
                    ->placeholder('-'),

                TextEntry::make('output_video_url')
                    ->label('Output Video URL')
                    ->state(fn (Input $record): ?string => self::resolveOutputVideoUrl($record))
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->copyable()
                    ->placeholder('-'),

                TextEntry::make('local_output_video_url')
                    ->label('Local Output Video URL')
                    ->state(fn (Input $record): ?string => self::resolveLocalOutputVideoUrl($record))
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->copyable()
                    ->placeholder('-'),

                TextEntry::make('download_output_video')
                    ->label('Download Output Video')
                    ->state('Download video')
                    ->url(fn (Input $record): ?string => self::resolveLocalOutputVideoUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Input $record): bool => self::resolveLocalOutputVideoUrl($record) !== null),

                TextEntry::make('created_at'),
                TextEntry::make('updated_at'),
            ]);
    }

    private static function resolveOutputVideoUrl(Input $record): ?string
    {
        $prediction = $record->prediction()->with('outputs.media')->first();
        if (! $prediction) {
            return null;
        }

        /** @var PredictionOutput|null $videoOutput */
        $videoOutput = $prediction->outputs
            ->first(fn (PredictionOutput $output) => $output->kind === 'video');

        if (! $videoOutput instanceof PredictionOutput) {
            return null;
        }

        $media = $videoOutput->getMediaFile();
        if ($media) {
            return $media->getFullUrl();
        }

        $path = (string) $videoOutput->path;
        if ($path !== '' && filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return null;
    }

    private static function resolveLocalOutputVideoUrl(Input $record): ?string
    {
        $prediction = $record->prediction()->with('outputs.media')->first();
        if (! $prediction) {
            return null;
        }

        /** @var PredictionOutput|null $videoOutput */
        $videoOutput = $prediction->outputs
            ->first(fn (PredictionOutput $output) => $output->kind === 'video');

        if (! $videoOutput instanceof PredictionOutput) {
            return null;
        }

        $media = $videoOutput->getMediaFile();

        return $media?->getFullUrl();
    }
}
