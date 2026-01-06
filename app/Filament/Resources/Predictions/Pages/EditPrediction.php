<?php

namespace App\Filament\Resources\Predictions\Pages;

use App\Filament\Resources\Predictions\PredictionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPrediction extends EditRecord
{
    protected static string $resource = PredictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
