<?php

namespace App\Filament\Resources\Predictions\Pages;

use App\Filament\Resources\Predictions\PredictionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPredictions extends ListRecords
{
    protected static string $resource = PredictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
