<?php

namespace App\Filament\Resources\Models\Pages;

use App\Filament\Resources\Models\ModelsResource;
use App\Filament\Support\SyncsLanguageTranslations;
use Filament\Resources\Pages\CreateRecord;

class CreateModels extends CreateRecord
{
    use SyncsLanguageTranslations;

    protected static string $resource = ModelsResource::class;

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
