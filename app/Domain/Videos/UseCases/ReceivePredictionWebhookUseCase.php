<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Broadcasting\Events\UserJobUpdatedBroadcast;
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
        $this->broadcastJobUpdated($prediction);

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
    ): array {
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
            PredictionStatus::CANCELLED => $this->handleCancelled($prediction),
            default => null,
        };
    }

    private function handleSucceeded(Prediction $prediction, PredictionWebhookDTO $dto): void
    {
        $outputUrl = $this->extractOutputUrl($dto);
        if ($outputUrl === null) {
            $this->repository->updatePrediction($prediction, [
                'status' => PredictionStatus::FAILED->value,
                'failed_at' => now(),
                'error_message' => 'Provider returned success without a usable output.',
            ]);
            $this->repository->updateInputStatus($prediction, Input::FAILED);
            $this->effects->refundUnsuccessfulGenerationIfCharged($prediction, 'Provider returned success without a usable output.', [
                'refund_reason' => 'missing_output',
            ]);

            return;
        }

        $this->repository->createOutput(
            $prediction,
            $outputUrl
        );
        $this->repository->updateInputStatus($prediction, Input::DONE);
        $this->effects->dispatchDownloadOutputs($prediction);
    }

    private function handleFailed(Prediction $prediction): void
    {
        $this->repository->updateInputStatus($prediction, Input::FAILED);
        $this->effects->refundUnsuccessfulGenerationIfCharged($prediction, 'Failed video generation', [
            'refund_reason' => 'provider_failed',
        ]);
    }

    private function handleCancelled(Prediction $prediction): void
    {
        $this->repository->updateInputStatus($prediction, Input::CANCELLED);
        $this->effects->refundUnsuccessfulGenerationIfCharged($prediction, 'Canceled video generation', [
            'refund_reason' => 'provider_cancelled',
        ]);
    }

    private function broadcastJobUpdated(Prediction $prediction): void
    {
        try {
            $input = $prediction->input()->first();
            if (! $input instanceof Input) {
                return;
            }

            event(UserJobUpdatedBroadcast::fromInput($input->refresh()));
        } catch (\Throwable) {
            // Broadcasting is a non-critical side-effect.
        }
    }

    private function extractOutputUrl(PredictionWebhookDTO $dto): ?string
    {
        $output = $dto->getOutput();

        if (is_string($output) && $output !== '' && filter_var($output, FILTER_VALIDATE_URL)) {
            return $output;
        }

        if (is_array($output)) {
            foreach ($output as $item) {
                if (is_string($item) && $item !== '' && filter_var($item, FILTER_VALIDATE_URL)) {
                    return $item;
                }
            }
        }

        return null;
    }
}
