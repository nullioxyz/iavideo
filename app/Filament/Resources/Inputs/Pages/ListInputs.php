<?php

namespace App\Filament\Resources\Inputs\Pages;

use App\Filament\Resources\Inputs\InputResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInputs extends ListRecords
{
    protected static string $resource = InputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
