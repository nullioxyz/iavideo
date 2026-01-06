<?php

namespace App\Filament\Resources\PredictionOutputs\Pages;

use App\Filament\Resources\PredictionOutputs\PredictionOutputResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPredictionOutput extends ViewRecord
{
    protected static string $resource = PredictionOutputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
