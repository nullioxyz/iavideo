<?php

namespace App\Domain\Videos\DTO;

final class InputCreateDTO
{
    public function __construct(
        public readonly int $presetId,
        public readonly ?string $originalFilename = null,
        public readonly ?string $mimeType = null,
        public readonly ?int $sizeBytes = null,
        public readonly ?string $startImagePath = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            presetId: (int) $data['preset_id'],
            startImagePath: (string) $data['start_image_path'],
            originalFilename: $data['original_filename'] ?? null,
            mimeType: $data['mime_type'] ?? null,
            sizeBytes: isset($data['size_bytes']) ? (int) $data['size_bytes'] : null,
        );
    }

    public function toArray(int $userId): array
    {
        return [
            'user_id' => $userId,
            'preset_id' => $this->presetId,
            'start_image_path' => $this->startImagePath,
            'original_filename' => $this->originalFilename,
            'mime_type' => $this->mimeType,
            'size_bytes' => $this->sizeBytes,
            'status' => 'created',
            'credit_debited' => false,
            'credit_ledger_id' => null,
        ];
    }
}
