<?php

namespace App\Filament\Resources\Presets\Pages;

use App\Filament\Resources\Presets\PresetsResource;
use App\Filament\Support\NotifiesAboutPendingMedia;
use App\Filament\Support\SyncsLanguageTranslations;
use Filament\Resources\Pages\CreateRecord;

class CreatePresets extends CreateRecord
{
    use NotifiesAboutPendingMedia;
    use SyncsLanguageTranslations;

    protected static string $resource = PresetsResource::class;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $translationsPayload = [];

    protected bool $hasPendingMediaUpload = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->translationsPayload = $this->pullTranslationsPayload($data);
        $this->hasPendingMediaUpload = filled($data['preview_image_upload_path'] ?? null)
            || filled($data['preview_video_upload_path'] ?? null);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncTranslations(
            $this->record,
            $this->translationsPayload,
            ['name', 'slug', 'prompt', 'negative_prompt']
        );

        if ($this->hasPendingMediaUpload) {
            $this->notifyPendingMedia(
                count(array_filter([
                    $this->record->preview_image_upload_path,
                    $this->record->preview_video_upload_path,
                ]))
            );
        }
    }
}
