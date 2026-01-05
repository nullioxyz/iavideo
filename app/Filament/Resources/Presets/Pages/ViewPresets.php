<?php

namespace App\Filament\Resources\Presets\Pages;

use App\Filament\Resources\Presets\PresetsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPresets extends ViewRecord
{
    protected static string $resource = PresetsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
