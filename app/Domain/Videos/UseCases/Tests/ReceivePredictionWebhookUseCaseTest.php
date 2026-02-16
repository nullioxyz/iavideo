<?php

namespace App\Domain\Videos\UseCases\Tests;

use App\Domain\Videos\Contracts\PredictionWebhookEffectsInterface;
use App\Domain\Videos\Contracts\Repositories\PredictionWebhookRepositoryInterface;
use App\Domain\Videos\DTO\PredictionWebhookDTO;
use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\UseCases\ReceivePredictionWebhookUseCase;
use PHPUnit\Framework\TestCase;

class ReceivePredictionWebhookUseCaseTest extends TestCase
{
    public function test_it_returns_early_when_prediction_is_terminal(): void
    {
        $prediction = new Prediction([
            'status' => 'succeeded',
            'input_id' => 1,
            'model_id' => 1,
            'source' => 'web',
            'attempt' => 1,
        ]);

        $repository = new class($prediction) implements PredictionWebhookRepositoryInterface
        {
            public int $updateCalls = 0;

            public function __construct(private ?Prediction $prediction) {}

            public function findByExternalId(string $externalId): ?Prediction
            {
                return $this->prediction;
            }

            public function updatePrediction(Prediction $prediction, array $data): Prediction
            {
                $this->updateCalls++;

                return $prediction;
            }

            public function createOutput(Prediction $prediction, string $path, string $kind = 'video'): void {}

            public function updateInputStatus(Prediction $prediction, string $status): void {}
        };

        $effects = new class implements PredictionWebhookEffectsInterface
        {
            public int $dispatchCalls = 0;

            public int $refundCalls = 0;

            public function dispatchDownloadOutputs(Prediction $prediction): void
            {
                $this->dispatchCalls++;
            }

            public function refundFailedGenerationIfDebited(Prediction $prediction): void
            {
                $this->refundCalls++;
            }
        };

        $useCase = new ReceivePredictionWebhookUseCase($repository, $effects);

        $dto = PredictionWebhookDTO::fromArray([
            'id' => 'pred-1',
            'version' => 'v1',
            'status' => 'processing',
        ]);

        $result = $useCase->execute($dto);

        $this->assertSame($prediction, $result);
        $this->assertSame(0, $repository->updateCalls);
        $this->assertSame(0, $effects->dispatchCalls);
        $this->assertSame(0, $effects->refundCalls);
    }

    public function test_it_normalizes_canceled_status_to_cancelled(): void
    {
        $prediction = new Prediction([
            'status' => 'processing',
            'input_id' => 1,
            'model_id' => 1,
            'source' => 'web',
            'attempt' => 1,
        ]);

        $repository = new class($prediction) implements PredictionWebhookRepositoryInterface
        {
            public array $lastUpdate = [];

            public function __construct(private ?Prediction $prediction) {}

            public function findByExternalId(string $externalId): ?Prediction
            {
                return $this->prediction;
            }

            public function updatePrediction(Prediction $prediction, array $data): Prediction
            {
                $this->lastUpdate = $data;
                if (isset($data['status'])) {
                    $prediction->status = $data['status'];
                }

                return $prediction;
            }

            public function createOutput(Prediction $prediction, string $path, string $kind = 'video'): void {}

            public function updateInputStatus(Prediction $prediction, string $status): void {}
        };

        $effects = new class implements PredictionWebhookEffectsInterface
        {
            public function dispatchDownloadOutputs(Prediction $prediction): void {}

            public function refundFailedGenerationIfDebited(Prediction $prediction): void {}
        };

        $useCase = new ReceivePredictionWebhookUseCase($repository, $effects);

        $dto = PredictionWebhookDTO::fromArray([
            'id' => 'pred-2',
            'version' => 'v1',
            'status' => 'canceled',
        ]);

        $useCase->execute($dto);

        $this->assertSame('cancelled', $repository->lastUpdate['status']);
        $this->assertArrayHasKey('canceled_at', $repository->lastUpdate);
    }

    public function test_it_handles_succeeded_with_output_side_effects(): void
    {
        $prediction = new Prediction([
            'status' => 'processing',
            'input_id' => 10,
            'model_id' => 20,
            'source' => 'web',
            'attempt' => 1,
        ]);

        $repository = new class($prediction) implements PredictionWebhookRepositoryInterface
        {
            public array $outputs = [];

            public array $inputStatuses = [];

            public function __construct(private ?Prediction $prediction) {}

            public function findByExternalId(string $externalId): ?Prediction
            {
                return $this->prediction;
            }

            public function updatePrediction(Prediction $prediction, array $data): Prediction
            {
                if (isset($data['status'])) {
                    $prediction->status = $data['status'];
                }

                return $prediction;
            }

            public function createOutput(Prediction $prediction, string $path, string $kind = 'video'): void
            {
                $this->outputs[] = compact('path', 'kind');
            }

            public function updateInputStatus(Prediction $prediction, string $status): void
            {
                $this->inputStatuses[] = $status;
            }
        };

        $effects = new class implements PredictionWebhookEffectsInterface
        {
            public int $dispatchCalls = 0;

            public function dispatchDownloadOutputs(Prediction $prediction): void
            {
                $this->dispatchCalls++;
            }

            public function refundFailedGenerationIfDebited(Prediction $prediction): void {}
        };

        $useCase = new ReceivePredictionWebhookUseCase($repository, $effects);

        $dto = PredictionWebhookDTO::fromArray([
            'id' => 'pred-3',
            'version' => 'v1',
            'status' => 'succeeded',
            'output' => 'https://cdn.example.com/video.mp4',
        ]);

        $useCase->execute($dto);

        $this->assertSame('done', $repository->inputStatuses[0]);
        $this->assertSame('https://cdn.example.com/video.mp4', $repository->outputs[0]['path']);
        $this->assertSame('video', $repository->outputs[0]['kind']);
        $this->assertSame(1, $effects->dispatchCalls);
    }

    public function test_it_handles_failed_with_refund_side_effect(): void
    {
        $prediction = new Prediction([
            'status' => 'processing',
            'input_id' => 10,
            'model_id' => 20,
            'source' => 'web',
            'attempt' => 1,
        ]);

        $repository = new class($prediction) implements PredictionWebhookRepositoryInterface
        {
            public array $inputStatuses = [];

            public array $lastUpdate = [];

            public function __construct(private ?Prediction $prediction) {}

            public function findByExternalId(string $externalId): ?Prediction
            {
                return $this->prediction;
            }

            public function updatePrediction(Prediction $prediction, array $data): Prediction
            {
                $this->lastUpdate = $data;
                if (isset($data['status'])) {
                    $prediction->status = $data['status'];
                }

                return $prediction;
            }

            public function createOutput(Prediction $prediction, string $path, string $kind = 'video'): void {}

            public function updateInputStatus(Prediction $prediction, string $status): void
            {
                $this->inputStatuses[] = $status;
            }
        };

        $effects = new class implements PredictionWebhookEffectsInterface
        {
            public int $refundCalls = 0;

            public function dispatchDownloadOutputs(Prediction $prediction): void {}

            public function refundFailedGenerationIfDebited(Prediction $prediction): void
            {
                $this->refundCalls++;
            }
        };

        $useCase = new ReceivePredictionWebhookUseCase($repository, $effects);

        $dto = PredictionWebhookDTO::fromArray([
            'id' => 'pred-4',
            'version' => 'v1',
            'status' => 'failed',
            'error' => 'provider error',
        ]);

        $useCase->execute($dto);

        $this->assertSame('failed', $repository->inputStatuses[0]);
        $this->assertSame('provider error', $repository->lastUpdate['error_message']);
        $this->assertSame(1, $effects->refundCalls);
    }

    public function test_it_throws_when_prediction_is_not_found(): void
    {
        $repository = new class implements PredictionWebhookRepositoryInterface
        {
            public function findByExternalId(string $externalId): ?Prediction
            {
                return null;
            }

            public function updatePrediction(Prediction $prediction, array $data): Prediction
            {
                return $prediction;
            }

            public function createOutput(Prediction $prediction, string $path, string $kind = 'video'): void {}

            public function updateInputStatus(Prediction $prediction, string $status): void {}
        };

        $effects = new class implements PredictionWebhookEffectsInterface
        {
            public function dispatchDownloadOutputs(Prediction $prediction): void {}

            public function refundFailedGenerationIfDebited(Prediction $prediction): void {}
        };

        $useCase = new ReceivePredictionWebhookUseCase($repository, $effects);

        $this->expectException(\RuntimeException::class);

        $useCase->execute(PredictionWebhookDTO::fromArray([
            'id' => 'missing',
            'version' => 'v1',
            'status' => 'processing',
        ]));
    }
}
