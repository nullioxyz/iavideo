<?php

namespace App\Domain\AIProviders\Tests\Jobs;

use App\Domain\Videos\Jobs\DownloadPredictionOutputsJob;
use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use App\Domain\Videos\Support\FakeHttpDownloader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;


class AttachPredictionOutputMediaJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('media-library.media_downloader', FakeHttpDownloader::class);
        Config::set('medialibrary.disk_name', 'media');

        // Garante diretório
        Storage::disk('media')->makeDirectory('/');
    }

     protected function tearDown(): void
    {
        // limpa artefatos do medialibrary no disk (ajuste conforme seu root)
        Storage::disk('media')->deleteDirectory('/');
        parent::tearDown();
    }

    public function test_job_should_download_and_attach_video_from_provider_url(): void
    {
        $prediction = Prediction::factory()->create([
            'source' => 'web',
            'status' => 'starting'
        ]);

        // URL "fake" (vai ser interceptada pelo Http::fake)
        $videoUrl = 'https://cdn.replicate.com/fake/video.mp4';

        $output = PredictionOutput::query()->create([
            'prediction_id' => $prediction->id,
            'kind' => 'video',
            'path' => $videoUrl,
        ]);

        // Fixture local (qualquer arquivo com extensão mp4 serve para o teste)
        $realVideo = base_path('tests/Fixtures/video.mp4');

        // Se quiser garantir que existe:
        $this->assertTrue(file_exists($realVideo), 'Fixture fake-video.mp4 not found');

        // Fake HTTP: retorna bytes do arquivo local como se fosse download
        Http::fake([
            'cdn.replicate.com/*' => Http::response(
                file_get_contents($realVideo),
                200,
                ['Content-Type' => 'video/mp4']
            ),
        ]);

        $job = new DownloadPredictionOutputsJob($output->prediction_id);
        $job->handle();

        $output->refresh();

        $media = $output->getFirstMedia('file');
        $this->assertNotNull($media);

        $this->assertSame('video/mp4', $media->mime_type);
        $this->assertNotEmpty($media->file_name);
        $this->assertGreaterThan(0, (int) $media->size);

        $this->assertCount(1, $output->getMedia('file'));
    }
}
