<?php

namespace App\Filament\Resources\Predictions\Pages;

use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use App\Filament\Resources\Predictions\PredictionResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPrediction extends ViewRecord
{
    protected static string $resource = PredictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('download_output_video')
                ->label('Download Output Video')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): ?string => $this->resolveLocalOutputVideoUrl())
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->resolveLocalOutputVideoUrl() !== null),
        ];
    }

    private function resolveLocalOutputVideoUrl(): ?string
    {
        /** @var Prediction $record */
        $record = $this->getRecord();

        $prediction = $record->loadMissing('outputs.media');

        /** @var PredictionOutput|null $videoOutput */
        $videoOutput = $prediction->outputs
            ->first(fn (PredictionOutput $output) => $output->kind === 'video');

        if (! $videoOutput instanceof PredictionOutput) {
            return null;
        }

        return $videoOutput->getMediaFile()?->getFullUrl();
    }
}
