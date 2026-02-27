<?php

namespace App\Filament\Resources\Seos\Pages;

use App\Filament\Resources\Seos\SeosResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeos extends ListRecords
{
    protected static string $resource = SeosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

