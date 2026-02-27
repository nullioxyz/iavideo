<?php

namespace App\Filament\Resources\Institutionals\Pages;

use App\Filament\Resources\Institutionals\InstitutionalsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstitutionals extends ListRecords
{
    protected static string $resource = InstitutionalsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

