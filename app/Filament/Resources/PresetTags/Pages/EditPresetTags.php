<?php

namespace App\Filament\Resources\PresetTags\Pages;

use App\Filament\Resources\PresetTags\PresetTagsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPresetTags extends EditRecord
{
    protected static string $resource = PresetTagsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

