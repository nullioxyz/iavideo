<?php

namespace App\Filament\Resources\Presets\Pages;

use App\Filament\Resources\Presets\PresetsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPresets extends EditRecord
{
    protected static string $resource = PresetsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
