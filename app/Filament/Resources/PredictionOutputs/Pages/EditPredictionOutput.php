<?php

namespace App\Filament\Resources\PredictionOutputs\Pages;

use App\Filament\Resources\PredictionOutputs\PredictionOutputResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPredictionOutput extends EditRecord
{
    protected static string $resource = PredictionOutputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
