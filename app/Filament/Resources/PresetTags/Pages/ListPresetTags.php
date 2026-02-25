<?php

namespace App\Filament\Resources\PresetTags\Pages;

use App\Filament\Resources\PresetTags\PresetTagsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPresetTags extends ListRecords
{
    protected static string $resource = PresetTagsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

