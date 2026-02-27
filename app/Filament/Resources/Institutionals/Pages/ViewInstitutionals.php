<?php

namespace App\Filament\Resources\Institutionals\Pages;

use App\Filament\Resources\Institutionals\InstitutionalsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInstitutionals extends ViewRecord
{
    protected static string $resource = InstitutionalsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

