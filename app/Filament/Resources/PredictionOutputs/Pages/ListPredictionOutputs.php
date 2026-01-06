<?php

namespace App\Filament\Resources\PredictionOutputs\Pages;

use App\Filament\Resources\PredictionOutputs\PredictionOutputResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPredictionOutputs extends ListRecords
{
    protected static string $resource = PredictionOutputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
