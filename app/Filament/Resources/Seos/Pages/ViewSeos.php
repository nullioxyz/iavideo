<?php

namespace App\Filament\Resources\Seos\Pages;

use App\Filament\Resources\Seos\SeosResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSeos extends ViewRecord
{
    protected static string $resource = SeosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

