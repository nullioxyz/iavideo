<?php

namespace App\Filament\Resources\Models\Pages;

use App\Filament\Resources\Models\ModelsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditModels extends EditRecord
{
    protected static string $resource = ModelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
