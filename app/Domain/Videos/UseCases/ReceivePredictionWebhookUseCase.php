<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Videos\Contracts\PredictionWebhookEffectsInterface;
use App\Domain\Videos\Contracts\Repositories\PredictionWebhookRepositoryInterface;
use App\Domain\Videos\DTO\PredictionWebhookDTO;
use App\Domain\Videos\Enums\PredictionStatus;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Support\Carbon;

final class ReceivePredictionWebhookUseCase
{
    public function __construct(
        private readonly PredictionWebhookRepositoryInterface $repository,
        private readonly PredictionWebhookEffectsInterface $effects,
    ) {}

    public function execute(PredictionWebhookDTO $dto): Prediction
    {
        $prediction = $this->repository->findByExternalId($dto->getId());
        if (! $prediction) {
            throw new \RuntimeException('Prediction not found for webhook payload.');
        }

        $payload = $dto->toArray();
        $status = PredictionStatus::fromWebhook($payload['status'] ?? null);

        if ($this->isTerminal($prediction->status)) {
            return $prediction;
        }

        $update = $this->buildUpdatePayload($prediction, $dto, $status, $payload);
        $prediction = $this->repository->updatePrediction($prediction, $update);

        $this->handleSideEffects($prediction, $status, $dto);

        return $prediction;
    }

    private function isTerminal(string $status): bool
    {
        return PredictionStatus::tryFrom($status)?->isTerminal() ?? false;
    }

    private function buildUpdatePayload(
        Prediction $prediction,
        PredictionWebhookDTO $dto,
        PredictionStatus $status,
        array $payload
    ): array
    {
        $now = Carbon::now();
        $update = $dto->prepareToSave(
            $prediction->input_id,
            $prediction->model_id,
            $prediction->source,
            $prediction->attempt,
            $dto->getOutput()
        );

        $update['status'] = $status->value;

        $update = array_merge(
            $update,
            $status->startsProcessingWindow() && ! $prediction->started_at ? ['started_at' => $now] : [],
            $status->shouldMarkFinished() ? ['finished_at' => $now] : [],
            $status->outcomeUpdate($now, $payload['error'] ?? null)
        );

        return $update;
    }

    private function handleSideEffects(Prediction $prediction, PredictionStatus $status, PredictionWebhookDTO $dto): void
    {
        match ($status) {
            PredictionStatus::SUCCEEDED => $this->handleSucceeded($prediction, $dto),
            PredictionStatus::FAILED => $this->handleFailed($prediction),
            default => null,
        };
    }

    private function handleSucceeded(Prediction $prediction, PredictionWebhookDTO $dto): void
    {
        $this->repository->createOutput(
            $prediction,
            (string) ($dto->getOutput() ?? 'empty-path')
        );
        $this->repository->updateInputStatus($prediction, Input::DONE);
        $this->effects->dispatchDownloadOutputs($prediction);
    }

    private function handleFailed(Prediction $prediction): void
    {
        $this->repository->updateInputStatus($prediction, Input::FAILED);
        $this->effects->refundFailedGenerationIfDebited($prediction);
    }
}
