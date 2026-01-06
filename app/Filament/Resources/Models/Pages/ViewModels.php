<?php

namespace App\Filament\Resources\Models\Pages;

use App\Filament\Resources\Models\ModelsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewModels extends ViewRecord
{
    protected static string $resource = ModelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
