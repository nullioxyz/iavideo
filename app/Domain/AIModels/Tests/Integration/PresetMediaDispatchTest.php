<?php

namespace App\Domain\AIModels\Tests\Integration;

use App\Domain\AIModels\Jobs\AttachPresetMediaJob;
use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PresetMediaDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_parallel_jobs_for_image_and_video_upload_paths(): void
    {
        Queue::fake();

        $model = AIModel::factory()->create([
            'active' => true,
        ]);

        Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'preview_image_upload_path' => 'presets/uploads/images/one.jpg',
            'preview_video_upload_path' => 'presets/uploads/videos/one.mp4',
        ]);

        Queue::assertPushed(AttachPresetMediaJob::class, function (AttachPresetMediaJob $job): bool {
            return $job->kind === 'image'
                && $job->path === 'presets/uploads/images/one.jpg';
        });

        Queue::assertPushed(AttachPresetMediaJob::class, function (AttachPresetMediaJob $job): bool {
            return $job->kind === 'video'
                && $job->path === 'presets/uploads/videos/one.mp4';
        });

        Queue::assertPushed(AttachPresetMediaJob::class, 2);
    }
}
