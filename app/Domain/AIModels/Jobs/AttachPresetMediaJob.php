<?php

namespace App\Domain\AIModels\Jobs;

use App\Domain\AIModels\Models\Preset;
use App\Infra\Storage\UploadStorageResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttachPresetMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly int $presetId,
        public readonly string $kind,
        public readonly string $path,
        public readonly ?string $disk = null,
    ) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60, 180];
    }

    public function handle(): void
    {
        $sourceDisk = $this->disk ?: UploadStorageResolver::mediaDisk();
        $targetDisk = UploadStorageResolver::mediaDisk();

        $preset = Preset::query()->find($this->presetId);

        if (! $preset instanceof Preset) {
            return;
        }

        if (! in_array($this->kind, ['image', 'video'], true)) {
            Log::warning('ai_models.presets.media.attach.skipped_invalid_kind', [
                'preset_id' => $this->presetId,
                'kind' => $this->kind,
            ]);

            return;
        }

        if (! Storage::disk($sourceDisk)->exists($this->path)) {
            Log::warning('ai_models.presets.media.attach.skipped_missing_file', [
                'preset_id' => $this->presetId,
                'kind' => $this->kind,
                'path' => $this->path,
                'disk' => $sourceDisk,
            ]);

            return;
        }

        $collection = $this->kind === 'image' ? 'preview_image' : 'preview_video';

        $preset
            ->addMediaFromDisk($this->path, $sourceDisk)
            ->usingName("preset_{$this->kind}_preview")
            ->toMediaCollection($collection, $targetDisk);

        if ($this->kind === 'video') {
            $preset->forceFill([
                'preview_video_url' => (string) $preset->getFirstMediaUrl('preview_video'),
            ])->save();
        }

        $column = $this->kind === 'image' ? 'preview_image_upload_path' : 'preview_video_upload_path';

        Preset::query()
            ->whereKey($preset->getKey())
            ->where($column, $this->path)
            ->update([
                $column => null,
            ]);

        Storage::disk($sourceDisk)->delete($this->path);
    }
}
