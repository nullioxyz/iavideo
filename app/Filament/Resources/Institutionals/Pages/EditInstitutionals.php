<?php

namespace App\Filament\Resources\Institutionals\Pages;

use App\Filament\Resources\Institutionals\InstitutionalsResource;
use App\Filament\Support\SyncsLanguageTranslations;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditInstitutionals extends EditRecord
{
    use SyncsLanguageTranslations;

    protected static string $resource = InstitutionalsResource::class;

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
            ['title', 'slug', 'subtitle', 'short_description', 'description']
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
            ['title', 'slug', 'subtitle', 'short_description', 'description']
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

