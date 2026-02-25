<?php

namespace App\Domain\Videos\Resources;

use App\Domain\Videos\Models\PredictionOutput;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PredictionOutput */
class PredictionOutputResource extends JsonResource
{
    public function toArray($request): array
    {
        $playbackUrl = $this->resolvePlaybackUrl($request);

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

    private function resolvePlaybackUrl($request): ?string
    {
        $media = $this->getMediaFile();

        if ($media) {
            return $this->toAbsoluteUrl((string) $media->getUrl(), $request);
        }

        $path = (string) $this->path;
        if ($path !== '' && filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if ($path !== '' && str_starts_with($path, '/')) {
            return $this->toAbsoluteUrl($path, $request);
        }

        return null;
    }

    private function toAbsoluteUrl(string $url, $request): string
    {
        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $baseUrl = rtrim((string) ($request?->getSchemeAndHttpHost() ?? config('app.url')), '/');

        return $baseUrl.'/'.ltrim($url, '/');
    }
}
