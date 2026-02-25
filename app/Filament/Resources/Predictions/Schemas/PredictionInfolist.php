<?php

namespace App\Filament\Resources\Predictions\Schemas;

use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PredictionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('input.id')
                    ->label('Input'),
                TextEntry::make('input.title')
                    ->label('Input Name')
                    ->placeholder('-'),
                TextEntry::make('model.name')
                    ->label('Model'),
                TextEntry::make('external_id')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('source')
                    ->badge(),
                TextEntry::make('attempt')
                    ->numeric(),
                TextEntry::make('retry_of_prediction_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('queued_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('started_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('finished_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('failed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('duration_seconds')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('processing_ms')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('total_ms')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('cost_estimate_usd')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('cost_actual_usd')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('error_code')
                    ->placeholder('-'),
                TextEntry::make('error_message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('output_video_url')
                    ->label('Output Video URL')
                    ->state(fn (Prediction $record): ?string => self::resolveOutputVideoUrl($record))
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->copyable()
                    ->placeholder('-'),
                TextEntry::make('local_output_video_url')
                    ->label('Local Output Video URL')
                    ->state(fn (Prediction $record): ?string => self::resolveLocalOutputVideoUrl($record))
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->copyable()
                    ->placeholder('-'),
                TextEntry::make('download_output_video')
                    ->label('Download Output Video')
                    ->state('Download video')
                    ->url(fn (Prediction $record): ?string => self::resolveLocalOutputVideoUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Prediction $record): bool => self::resolveLocalOutputVideoUrl($record) !== null),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    private static function resolveOutputVideoUrl(Prediction $record): ?string
    {
        $prediction = $record->loadMissing('outputs.media');

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

    private static function resolveLocalOutputVideoUrl(Prediction $record): ?string
    {
        $prediction = $record->loadMissing('outputs.media');

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
