<?php

namespace App\Domain\Videos\DTO;

use Illuminate\Support\Carbon;

final class PredictionWebhookDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $version,
        public readonly ?string $status,
        public readonly array $input = [],
        public readonly ?array $urls = [],
        public readonly ?string $output = null,
        public readonly ?string $error = null,
        public readonly ?string $logs = null,
        public readonly ?array $metrics = [],
        public readonly ?string $created_at = null,
        public readonly ?string $started_at = null,
        public readonly ?string $completed_at = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            version: (string) $data['version'],
            status: $data['status'] ?? null,
            input: $data['input'] ?? [],
            urls: $data['urls'] ?? [],
            output: $data['output'] ?? null,
            error: $data['error'] ?? null,
            logs: $data['logs'] ?? null,
            metrics: $data['metrics'] ?? [],
            created_at: $data['created_at'] ?? null,
            started_at: $data['started_at'] ?? null,
            completed_at: $data['completed_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'status' => $this->status,

            'input' => $this->input,
            'output' => $this->output,
            'error' => $this->error,
            'logs' => $this->logs,
            'metrics' => $this->metrics,

            'created_at' => $this->created_at,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
        ];
    }

    public function prepareToSave(
        int $inputId,
        int $modelId,
        string $source,
        int $attempt = 1,
        ?string $output = null,
        ?int $retryOfPredictionId = null,
        ?float $costEstimateUsd = null,
        ?float $costActualUsd = null,
        ?string $errorCode = null,
    ): array {
        $failedAt = null;
        $finishedAt = null;

        if ($this->error !== null) {
            $failedAt = $this->getCompletedAt() ?? $this->getStartedAt() ?? $this->getCreatedAt();
        } elseif ($this->getCompletedAt() !== null) {
            $finishedAt = $this->getCompletedAt();
        }

        return [
            'input_id' => $inputId,
            'model_id' => $modelId,
            'external_id' => $this->getId(),
            'status' => $this->getStatus(),
            'source' => $source,
            'attempt' => $attempt,
            'output' => $output,
            'retry_of_prediction_id' => $retryOfPredictionId,

            'started_at' => $this->getStartedAt(),

            'finished_at' => $finishedAt,

            'failed_at' => $failedAt,

            'cost_estimate_usd' => $costEstimateUsd,
            'cost_actual_usd' => $costActualUsd,

            'error_code' => $errorCode,

            'error_message' => $this->getError(),

            'request_payload' => [
                'version' => $this->getVersion(),
                'input' => $this->getInput(),
            ],

            'response_payload' => $this->toProviderArray(),
        ];
    }

    public function toProviderArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'version' => $this->version,
            'created_at' => $this->created_at,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'status' => $this->status,
            'input' => $this->input,
            'output' => $this->output,
            'error' => $this->error,
            'logs' => $this->logs,
            'metrics' => $this->metrics,
        ], static fn ($v) => $v !== null);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getInput(): array
    {
        return $this->input;
    }

    public function getOutput(): mixed
    {
        return $this->output;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getLogs(): ?string
    {
        return $this->logs;
    }

    public function getMetrics(): ?array
    {
        return $this->metrics;
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->created_at ? Carbon::createFromTimeString($this->created_at) : null;
    }

    public function getStartedAt(): ?Carbon
    {
        return $this->started_at ? Carbon::createFromTimeString($this->started_at) : null;
    }

    public function getCompletedAt(): ?Carbon
    {
        return $this->completed_at ? Carbon::createFromTimeString($this->completed_at) : null;
    }

    public function getUrls(): array
    {
        return $this->urls;
    }
}
