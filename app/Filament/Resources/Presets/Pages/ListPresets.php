<?php

namespace App\Filament\Resources\Presets\Pages;

use App\Filament\Resources\Presets\PresetsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPresets extends ListRecords
{
    protected static string $resource = PresetsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
