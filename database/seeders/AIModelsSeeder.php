<?php

namespace Database\Seeders;

use App\Domain\AIModels\Models\Model;
use App\Domain\Platforms\Models\Platform;
use Illuminate\Database\Seeder;

class AIModelsSeeder extends Seeder
{
    public function run(): void
    {
        $platform = Platform::query()->updateOrCreate(
            ['slug' => 'replicate'],
            [
                'name' => 'Replicate',
                'updated_at' => now(),
            ]
        );

        $models = [
            [
                'name' => 'Veo 3',
                'slug' => 'google/veo-3',
                'provider_model_key' => 'google/veo-3',
                'cost_per_second_usd' => '0.4000',
                'credits_per_second' => '1.1429',
                'active' => true,
                'public_visible' => true,
                'default' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Kling v1.6 Standard',
                'slug' => 'kwaivgi/kling-v1.6-standard',
                'provider_model_key' => 'kwaivgi/kling-v1.6-standard',
                'cost_per_second_usd' => '0.0500',
                'credits_per_second' => '0.1429',
                'active' => true,
                'public_visible' => true,
                'default' => false,
                'sort_order' => 20,
            ],
            [
                'name' => 'Kling v2.1',
                'slug' => 'kwaivgi/kling-v2.1',
                'provider_model_key' => 'kwaivgi/kling-v2.1',
                'cost_per_second_usd' => '0.0500',
                'credits_per_second' => '0.1429',
                'active' => true,
                'public_visible' => true,
                'default' => false,
                'sort_order' => 30,
            ],
            [
                'name' => 'Veo 3.1 Fast',
                'slug' => 'google/veo-3.1-fast',
                'provider_model_key' => 'google/veo-3.1-fast',
                'cost_per_second_usd' => '0.1500',
                'credits_per_second' => '0.4286',
                'active' => true,
                'public_visible' => true,
                'default' => false,
                'sort_order' => 40,
            ],
            [
                'name' => 'Wan 2.2 I2V Fast',
                'slug' => 'wan-video/wan-2.2-i2v-fast',
                'provider_model_key' => 'wan-video/wan-2.2-i2v-fast',
                'cost_per_second_usd' => null,
                'credits_per_second' => null,
                'active' => false,
                'public_visible' => false,
                'default' => false,
                'sort_order' => 50,
            ],
            [
                'name' => 'Veo 3.1',
                'slug' => 'google/veo-3.1',
                'provider_model_key' => 'google/veo-3.1',
                'cost_per_second_usd' => '0.4000',
                'credits_per_second' => '1.1429',
                'active' => true,
                'public_visible' => true,
                'default' => false,
                'sort_order' => 60,
            ],
            [
                'name' => 'Kling v2.5 Turbo Pro',
                'slug' => 'kwaivgi/kling-v2.5-turbo-pro',
                'provider_model_key' => 'kwaivgi/kling-v2.5-turbo-pro',
                'cost_per_second_usd' => '0.0700',
                'credits_per_second' => '0.2000',
                'active' => true,
                'public_visible' => true,
                'default' => false,
                'sort_order' => 70,
            ],
            [
                'name' => 'P-Video',
                'slug' => 'prunaai/p-video',
                'provider_model_key' => 'prunaai/p-video',
                'cost_per_second_usd' => '0.0400',
                'credits_per_second' => '0.1143',
                'active' => true,
                'public_visible' => true,
                'default' => false,
                'sort_order' => 80,
            ],
            [
                'name' => 'Veo 2',
                'slug' => 'google/veo-2',
                'provider_model_key' => 'google/veo-2',
                'cost_per_second_usd' => '0.5000',
                'credits_per_second' => '1.4286',
                'active' => true,
                'public_visible' => true,
                'default' => false,
                'sort_order' => 90,
            ],
        ];

        foreach ($models as $attributes) {
            Model::query()->updateOrCreate(
                [
                    'platform_id' => $platform->getKey(),
                    'provider_model_key' => $attributes['provider_model_key'],
                ],
                array_merge($attributes, [
                    'platform_id' => $platform->getKey(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
