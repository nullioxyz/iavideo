<?php

namespace App\Filament\Resources\Seos\Pages;

use App\Filament\Resources\Seos\SeosResource;
use App\Filament\Support\FilamentUpload;
use App\Filament\Support\NotifiesAboutPendingMedia;
use App\Filament\Support\SyncsLanguageTranslations;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateSeos extends CreateRecord
{
    use NotifiesAboutPendingMedia;
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->translationsPayload = $this->pullTranslationsPayload($data);
        $this->imageUploadPaths = array_values(array_filter($data['images_upload_paths'] ?? []));
        unset($data['images_upload_paths']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $sourceDisk = FilamentUpload::disk();
        $targetDisk = FilamentUpload::mediaDisk();

        $this->syncTranslations(
            $this->record,
            $this->translationsPayload,
            ['slug', 'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description', 'twitter_title', 'twitter_description']
        );

        foreach ($this->imageUploadPaths as $path) {
            if (! is_string($path) || $path === '' || ! Storage::disk($sourceDisk)->exists($path)) {
                continue;
            }

            $this->record
                ->addMediaFromDisk($path, $sourceDisk)
                ->toMediaCollection('images', $targetDisk);

            Storage::disk($sourceDisk)->delete($path);
        }

        if ($this->imageUploadPaths !== []) {
            $this->notifyPendingMedia(count($this->imageUploadPaths));
        }
    }
}
