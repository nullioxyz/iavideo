<?php

namespace App\Filament\Resources\Models\Pages;

use App\Filament\Resources\Models\ModelsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListModels extends ListRecords
{
    protected static string $resource = ModelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
