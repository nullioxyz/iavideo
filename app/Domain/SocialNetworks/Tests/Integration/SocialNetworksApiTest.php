<?php

namespace App\Domain\SocialNetworks\Tests\Integration;

use App\Domain\SocialNetworks\Models\SocialNetwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialNetworksApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_returns_only_active_social_networks(): void
    {
        $instagram = SocialNetwork::query()->create([
            'url' => 'https://instagram.com/inkai',
            'slug' => 'instagram',
            'active' => true,
        ]);

        SocialNetwork::query()->create([
            'url' => 'https://x.com/inkai',
            'slug' => 'x',
            'active' => false,
        ]);

        $response = $this->getJson('/api/social-networks');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $instagram->getKey());
        $response->assertJsonPath('data.0.slug', 'instagram');
        $response->assertJsonPath('data.0.active', true);
    }
}
