<?php

namespace App\Filament\Resources\PresetTags\Pages;

use App\Filament\Resources\PresetTags\PresetTagsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPresetTags extends ViewRecord
{
    protected static string $resource = PresetTagsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

