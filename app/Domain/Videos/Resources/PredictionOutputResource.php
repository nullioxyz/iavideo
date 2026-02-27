<?php

namespace App\Domain\Videos\Resources;

use App\Domain\Videos\Models\PredictionOutput;
use App\Support\FrontendAssetUrl;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PredictionOutput */
class PredictionOutputResource extends JsonResource
{
    public function toArray($request): array
    {
        $playbackUrl = $this->resolvePlaybackUrl();

        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'path' => $this->path,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'file_url' => $playbackUrl,
            'playback_url' => $playbackUrl,
            'original_url' => $this->path,
            'is_local_media_ready' => $this->getMediaFile() !== null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function resolvePlaybackUrl(): ?string
    {
        $media = $this->getMediaFile();

        if ($media) {
            return FrontendAssetUrl::video($media);
        }

        $path = (string) $this->path;
        if ($path !== '' && filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if ($path !== '' && str_starts_with($path, '/')) {
            return FrontendAssetUrl::resolveExternal($path);
        }

        return null;
    }
}
