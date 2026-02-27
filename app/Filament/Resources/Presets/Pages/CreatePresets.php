<?php

namespace App\Filament\Resources\Presets\Pages;

use App\Filament\Resources\Presets\PresetsResource;
use App\Filament\Support\SyncsLanguageTranslations;
use Filament\Resources\Pages\CreateRecord;

class CreatePresets extends CreateRecord
{
    use SyncsLanguageTranslations;

    protected static string $resource = PresetsResource::class;

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
        $this->syncTranslations(
            $this->record,
            $this->translationsPayload,
            ['name', 'slug', 'prompt', 'negative_prompt']
        );
    }
}
