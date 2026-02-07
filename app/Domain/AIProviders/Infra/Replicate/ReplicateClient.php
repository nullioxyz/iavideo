<?php

namespace App\Domain\AIProviders\Infra\Replicate;

use App\Domain\AIProviders\Contracts\ProviderClientInterface;
use App\Domain\AIProviders\DTO\ProviderCreateResultDTO;
use App\Domain\AIProviders\DTO\ProviderGetResultDTO;
use Illuminate\Support\Facades\Http;

final class ReplicateClient implements ProviderClientInterface
{
    public function providerSlug(): string
    {
        return 'replicate';
    }

    public function create(string $modelSlug, array $payload, array $headers = []): ProviderCreateResultDTO
    {
        $url = "https://api.replicate.com/v1/models/{$modelSlug}/predictions";

        $res = Http::withToken(config('services.replicate.token'))
            ->acceptJson()
            ->withHeaders(array_merge([
                'Content-Type' => 'application/json',
            ], $headers))
            ->post($url, $payload);

        $json = $res->json() ?? [];

        return new ProviderCreateResultDTO(
            externalId: (string) ($json['id'] ?? ''),
            status: (string) ($json['status'] ?? ($res->successful() ? 'submitting' : 'failed')),
            responsePayload: $json
        );
    }

    public function get(string $externalId): ProviderGetResultDTO
    {
        $url = "https://api.replicate.com/v1/predictions/{$externalId}";

        $res = Http::withToken(config('services.replicate.token'))
            ->acceptJson()
            ->get($url);

        return new ProviderGetResultDTO(
            statusCode: $res->status(),
            payload: $res->json() ?? []
        );
    }

    public function cancel(string $externalId): ProviderGetResultDTO
    {
        $url = "https://api.replicate.com/v1/predictions/{$externalId}/cancel";

        $res = Http::withToken(config('services.replicate.token'))
            ->acceptJson()
            ->post($url);

        return new ProviderGetResultDTO(
            statusCode: $res->status(),
            payload: $res->json() ?? []
        );
    }
}
