<?php

namespace App\Domain\AIProviders\Infra\Replicate;

use App\Domain\AIProviders\Contracts\ProviderClientInterface;
use App\Domain\AIProviders\DTO\ProviderCreateResultDTO;
use App\Domain\AIProviders\DTO\ProviderGetResultDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $json = $res->json();
        if (! is_array($json)) {
            $json = [];
        }

        $json['_status_code'] = $res->status();
        $json['_ok'] = $res->successful();
        $json['_raw_body'] = (string) $res->body();

        $externalId = $this->extractExternalId($json);
        $status = (string) ($json['status'] ?? ($json['prediction']['status'] ?? ($res->successful() ? 'submitting' : 'failed')));

        if ($externalId === '') {
            Log::warning('replicate.create.missing_external_id', [
                'model_slug' => $modelSlug,
                'status_code' => $res->status(),
                'response' => $json,
            ]);
        }

        return new ProviderCreateResultDTO(
            externalId: $externalId,
            status: $status,
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

    private function extractExternalId(array $json): string
    {
        $candidate = (string) ($json['id'] ?? ($json['prediction']['id'] ?? ''));
        if ($candidate !== '') {
            return $candidate;
        }

        $getUrl = (string) ($json['urls']['get'] ?? '');
        if ($getUrl !== '') {
            $parts = explode('/', trim($getUrl, '/'));
            $last = end($parts);

            return is_string($last) ? $last : '';
        }

        return '';
    }
}
