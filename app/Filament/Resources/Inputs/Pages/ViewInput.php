<?php

namespace App\Filament\Resources\Inputs\Pages;

use App\Domain\Videos\Events\CreatePredictionForInput;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\PredictionOutput;
use App\Filament\Resources\Inputs\InputResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInput extends ViewRecord
{
    protected static string $resource = InputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('retry_prediction')
                ->label('Retry Prediction')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(function (): bool {
                    /** @var Input $record */
                    $record = $this->getRecord();

                    return in_array($record->status, [Input::FAILED, Input::PROCESSING], true);
                })
                ->action(function (): void {
                    /** @var Input $record */
                    $record = $this->getRecord();

                    try {
                        CreatePredictionForInput::dispatch((int) $record->getKey());

                        Notification::make()
                            ->title('Retry enfileirado')
                            ->body("Novo processamento solicitado para o input #{$record->getKey()}.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Falha ao enfileirar retry')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('download_output_video')
                ->label('Download Output Video')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): ?string => $this->resolveLocalOutputVideoUrl())
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->resolveLocalOutputVideoUrl() !== null),
        ];
    }

    private function resolveOutputVideoUrl(): ?string
    {
        /** @var Input $record */
        $record = $this->getRecord();

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

    private function resolveLocalOutputVideoUrl(): ?string
    {
        /** @var Input $record */
        $record = $this->getRecord();

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
