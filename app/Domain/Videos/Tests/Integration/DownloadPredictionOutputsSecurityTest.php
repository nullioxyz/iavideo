<?php

namespace App\Domain\Videos\Tests\Integration;

use App\Domain\Videos\Jobs\DownloadPredictionOutputsJob;
use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadPredictionOutputsSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_non_allowlisted_output_host(): void
    {
        config()->set('services.replicate.output_allowed_hosts', 'cdn.replicate.com');

        $prediction = Prediction::factory()->create();

        PredictionOutput::factory()->create([
            'prediction_id' => $prediction->getKey(),
            'kind' => 'video',
            'path' => 'https://example.com/malicious.mp4',
        ]);

        $job = new DownloadPredictionOutputsJob((int) $prediction->getKey());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not allowed');

        $job->handle();
    }
}

