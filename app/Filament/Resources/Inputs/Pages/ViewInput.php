<?php

namespace App\Filament\Resources\Inputs\Pages;

use App\Filament\Resources\Inputs\InputResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInput extends ViewRecord
{
    protected static string $resource = InputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
