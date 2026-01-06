<?php

namespace App\Filament\Resources\Inputs\Pages;

use App\Filament\Resources\Inputs\InputResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInput extends EditRecord
{
    protected static string $resource = InputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
