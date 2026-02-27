<?php

namespace App\Filament\Resources\PresetTags\Pages;

use App\Filament\Resources\PresetTags\PresetTagsResource;
use App\Filament\Support\SyncsLanguageTranslations;
use Filament\Resources\Pages\CreateRecord;

class CreatePresetTags extends CreateRecord
{
    use SyncsLanguageTranslations;

    protected static string $resource = PresetTagsResource::class;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $translationsPayload = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->translationsPayload = $this->pullTranslationsPayload($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncTranslations($this->record, $this->translationsPayload, ['name', 'slug']);
    }
}
