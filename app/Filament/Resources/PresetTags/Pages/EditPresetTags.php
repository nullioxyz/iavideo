<?php

namespace App\Filament\Resources\PresetTags\Pages;

use App\Filament\Resources\PresetTags\PresetTagsResource;
use App\Filament\Support\SyncsLanguageTranslations;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPresetTags extends EditRecord
{
    use SyncsLanguageTranslations;

    protected static string $resource = PresetTagsResource::class;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $translationsPayload = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['translations_payload'] = $this->buildTranslationsPayload($this->record, ['name', 'slug']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->translationsPayload = $this->pullTranslationsPayload($data);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncTranslations($this->record, $this->translationsPayload, ['name', 'slug']);
    }
}
