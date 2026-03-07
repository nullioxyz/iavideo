<?php

namespace App\Domain\Videos\Listeners\Tests;

use App\Domain\Videos\Events\CreatePredictionForInput;
use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Listeners\UploadInputImageListener;
use App\Domain\Videos\Models\Input;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadInputImageListenerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Storage::disk('local')->deleteDirectory('tmp/inputs');
        parent::tearDown();
    }

    public function test_it_attaches_media_updates_input_and_deletes_temp_file(): void
    {
        Event::fake([CreatePredictionForInput::class]);
        Storage::disk('local')->deleteDirectory('tmp/inputs');

        $input = Input::factory()->create([
            'status' => 'created',
            'start_image_path' => null,
        ]);

        $uploaded = UploadedFile::fake()->image('start.png', 900, 1600);
        $tempPath = "tmp/inputs/{$input->id}/start.png";

        Storage::disk('local')->putFileAs(
            dirname($tempPath),
            $uploaded,
            basename($tempPath)
        );

        $absolute = Storage::disk('local')->path($tempPath);
        $this->assertTrue(file_exists($absolute));

        $event = new InputCreated($input->id, $tempPath);
        $listener = new UploadInputImageListener;

        $listener->handle($event);

        $input->refresh();

        $this->assertSame('processing', $input->status);
        $this->assertNotNull($input->start_image_path);
        $this->assertNotEmpty($input->start_image_path);

        $this->assertCount(1, $input->getMedia('start_image'));

        $this->assertFalse(file_exists($absolute));
    }

    public function test_it_marks_input_failed_when_temp_file_is_missing(): void
    {
        $input = Input::factory()->create([
            'status' => 'created',
            'start_image_path' => null,
        ]);

        $missingPath = "tmp/inputs/{$input->id}/missing.png";

        $event = new InputCreated($input->id, $missingPath);
        $listener = new UploadInputImageListener;

        $listener->handle($event);

        $input->refresh();
        $this->assertSame('failed', $input->status);
        $this->assertNull($input->start_image_path);
        $this->assertCount(0, $input->getMedia('start_image'));
    }

    public function test_it_uses_spaces_with_inkai_prefix_in_production_mode(): void
    {
        Event::fake([CreatePredictionForInput::class]);
        Storage::fake('spaces');
        Storage::disk('local')->deleteDirectory('tmp/inputs');

        config()->set('uploads.provider', 's3');
        config()->set('uploads.s3.media_disk', 'spaces');
        config()->set('uploads.s3.media_prefix', '');
        $this->app['env'] = 'production';

        $input = Input::factory()->create([
            'status' => 'created',
            'start_image_path' => null,
        ]);

        $uploaded = UploadedFile::fake()->image('start.png', 900, 1600);
        $tempPath = "tmp/inputs/{$input->id}/start.png";

        Storage::disk('local')->putFileAs(
            dirname($tempPath),
            $uploaded,
            basename($tempPath)
        );

        $listener = new UploadInputImageListener;
        $listener->handle(new InputCreated($input->id, $tempPath));

        $input->refresh();
        $media = $input->getFirstMedia('start_image');

        $this->assertNotNull($media);
        $this->assertSame('spaces', $media->disk);
        $this->assertStringStartsWith('inkai/', $media->getPathRelativeToRoot());
        Storage::disk('spaces')->assertExists($media->getPathRelativeToRoot());
    }
}
