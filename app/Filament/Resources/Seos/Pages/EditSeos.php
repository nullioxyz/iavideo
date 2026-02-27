<?php

namespace App\Filament\Resources\Seos\Pages;

use App\Filament\Resources\Seos\SeosResource;
use App\Filament\Support\SyncsLanguageTranslations;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditSeos extends EditRecord
{
    use SyncsLanguageTranslations;

    protected static string $resource = SeosResource::class;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $translationsPayload = [];

    /**
     * @var list<string>
     */
    protected array $imageUploadPaths = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['translations_payload'] = $this->buildTranslationsPayload(
            $this->record,
            ['slug', 'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description', 'twitter_title', 'twitter_description']
        );

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->translationsPayload = $this->pullTranslationsPayload($data);
        $this->imageUploadPaths = array_values(array_filter($data['images_upload_paths'] ?? []));
        unset($data['images_upload_paths']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncTranslations(
            $this->record,
            $this->translationsPayload,
            ['slug', 'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description', 'twitter_title', 'twitter_description']
        );

        foreach ($this->imageUploadPaths as $path) {
            if (! is_string($path) || $path === '' || ! Storage::disk('public')->exists($path)) {
                continue;
            }

            $this->record
                ->addMediaFromDisk($path, 'public')
                ->toMediaCollection('images', 'public');
        }
    }
}

