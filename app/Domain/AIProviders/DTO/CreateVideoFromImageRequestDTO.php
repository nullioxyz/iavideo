<?php

namespace App\Domain\AIProviders\DTO;

final class CreateVideoFromImageRequestDTO
{
    public function __construct(
        public readonly string $modelSlug,
        public readonly ?string $modelVersion,
        public readonly string $imageUrl,
        public readonly string $prompt,
        public readonly ?string $negativePrompt,
        public readonly string $aspectRatio,
        public readonly int $durationSeconds,
        public readonly array $extra = [],
    ) {}
}
