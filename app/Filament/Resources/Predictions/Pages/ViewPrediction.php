<?php

namespace App\Filament\Resources\Predictions\Pages;

use App\Filament\Resources\Predictions\PredictionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPrediction extends ViewRecord
{
    protected static string $resource = PredictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
