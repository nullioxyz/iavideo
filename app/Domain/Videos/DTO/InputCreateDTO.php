<?php

namespace App\Domain\Videos\DTO;

final class InputCreateDTO
{
    public function __construct(
        public readonly int $modelId,
        public readonly int $presetId,
        public readonly ?int $durationSeconds = null,
        public readonly ?string $title = null,
        public readonly ?string $originalFilename = null,
        public readonly ?string $mimeType = null,
        public readonly ?int $sizeBytes = null,
        public readonly ?string $startImagePath = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: (int) $data['model_id'],
            presetId: (int) $data['preset_id'],
            durationSeconds: isset($data['duration_seconds']) ? (int) $data['duration_seconds'] : null,
            startImagePath: (string) $data['start_image_path'],
            title: $data['title'] ?? null,
            originalFilename: $data['original_filename'] ?? null,
            mimeType: $data['mime_type'] ?? null,
            sizeBytes: isset($data['size_bytes']) ? (int) $data['size_bytes'] : null,
        );
    }

    public function toArray(int $userId): array
    {
        return [
            'user_id' => $userId,
            'model_id' => $this->modelId,
            'preset_id' => $this->presetId,
            'start_image_path' => $this->startImagePath,
            'title' => $this->title ?? $this->originalFilename,
            'original_filename' => $this->originalFilename,
            'mime_type' => $this->mimeType,
            'size_bytes' => $this->sizeBytes,
            'duration_seconds' => $this->durationSeconds,
            'status' => 'created',
            'credit_debited' => false,
            'credit_ledger_id' => null,
            'estimated_cost_usd' => null,
            'model_cost_per_second_usd' => null,
            'model_credits_per_second' => null,
            'credits_charged' => 0,
            'billing_status' => 'none',
        ];
    }
}
