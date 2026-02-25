<?php

namespace App\Domain\AIProviders\Tests\Unit;

use App\Domain\AIProviders\Infra\Replicate\ReplicateClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReplicateClientTest extends TestCase
{
    public function test_create_extracts_external_id_from_urls_get_when_id_is_missing(): void
    {
        Config::set('services.replicate.token', 'test-token');

        Http::fake([
            'https://api.replicate.com/v1/models/*/predictions' => Http::response([
                'status' => 'starting',
                'urls' => [
                    'get' => 'https://api.replicate.com/v1/predictions/abc123',
                ],
            ], 201),
        ]);

        $client = new ReplicateClient;

        $result = $client->create('foo/bar', [
            'input' => ['prompt' => 'x'],
        ]);

        $this->assertSame('abc123', $result->externalId);
        $this->assertSame('starting', $result->status);
    }

    public function test_create_keeps_model_endpoint_even_when_version_is_present(): void
    {
        Config::set('services.replicate.token', 'test-token');

        Http::fake(function ($request) {
            $this->assertSame('https://api.replicate.com/v1/models/foo/bar/predictions', (string) $request->url());

            return Http::response([
                'id' => 'pred-1',
                'status' => 'starting',
            ], 201);
        });

        $client = new ReplicateClient;

        $result = $client->create('foo/bar', [
            'version' => 'v1',
            'input' => ['prompt' => 'x'],
        ]);

        $this->assertSame('pred-1', $result->externalId);
    }
}
