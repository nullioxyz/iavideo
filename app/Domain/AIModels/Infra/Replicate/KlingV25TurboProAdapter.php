<?php

namespace App\Domain\AIModels\Infra\Replicate;

use App\Domain\AIModels\Contracts\Infra\VideoModelAdapterInterface;
use App\Domain\AIProviders\DTO\CreateVideoFromImageRequestDTO;
use App\Domain\AIProviders\DTO\ModelCreateCommandDTO;
use App\Domain\AIProviders\DTO\ModelGetResultDTO;

final class KlingV25TurboProAdapter implements VideoModelAdapterInterface
{
    public function providerSlug(): string
    {
        return 'replicate';
    }

    public function modelSlug(): string
    {
        return 'kwaivgi/kling-v2.5-turbo-pro';
    }

    public function buildCreateCommand(CreateVideoFromImageRequestDTO $request): ModelCreateCommandDTO
    {
        $payload = [
            'input' => array_filter([
                'prompt' => $request->prompt,
                'image' => $request->imageUrl,
                'negative_prompt' => $request->negativePrompt,
                'aspect_ratio' => $request->aspectRatio,
                'duration' => $request->durationSeconds,
            ], fn ($v) => $v !== null && $v !== ''),
            ...$request->extra,
        ];

        $headers = [];

        return new ModelCreateCommandDTO($payload, $headers);
    }

    public function mapGetResult(array $providerResponse): ModelGetResultDTO
    {
        $status = (string) ($providerResponse['status'] ?? 'processing');

        $output = $providerResponse['output'] ?? null;
        $urls = is_array($output) ? $output : ($output ? [$output] : null);

        return new ModelGetResultDTO(
            status: $status,
            outputUrls: $urls,
            errorMessage: $providerResponse['error'] ?? null,
            raw: $providerResponse
        );
    }
}
