<?php

namespace App\Filament\Resources\Presets\Pages;

use App\Filament\Resources\Presets\PresetsResource;
use App\Filament\Support\SyncsLanguageTranslations;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPresets extends EditRecord
{
    use SyncsLanguageTranslations;

    protected static string $resource = PresetsResource::class;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $translationsPayload = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['translations_payload'] = $this->buildTranslationsPayload(
            $this->record,
            ['name', 'slug', 'prompt', 'negative_prompt']
        );

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->translationsPayload = $this->pullTranslationsPayload($data);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncTranslations(
            $this->record,
            $this->translationsPayload,
            ['name', 'slug', 'prompt', 'negative_prompt']
        );
    }
}
