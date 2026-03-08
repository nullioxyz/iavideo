<?php

namespace App\Domain\Credits\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Credits\Services\GenerationPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerationPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_credits_from_model_rate_for_five_seconds(): void
    {
        $model = AIModel::factory()->create([
            'cost_per_second_usd' => '0.1500',
            'credits_per_second' => '0.6000',
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'duration_seconds' => 5,
            'active' => true,
        ]);

        /** @var GenerationPricingService $service */
        $service = app(GenerationPricingService::class);

        $quote = $service->quote($model, $preset, 5);

        $this->assertSame(3, $quote->creditsRequired);
        $this->assertSame(5, $quote->durationSeconds);
        $this->assertSame('0.1500', $quote->modelCostPerSecondUsd);
        $this->assertSame('0.6000', $quote->modelCreditsPerSecond);
        $this->assertSame('0.7500', $quote->generationCostUsd);
    }

    public function test_it_calculates_twenty_five_credits_for_five_seconds_with_rate_five(): void
    {
        $model = AIModel::factory()->create([
            'cost_per_second_usd' => '0.0700',
            'credits_per_second' => '5.0000',
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'duration_seconds' => 5,
            'active' => true,
        ]);

        $quote = app(GenerationPricingService::class)->quote($model, $preset, 5);

        $this->assertSame(25, $quote->creditsRequired);
    }

    public function test_it_calculates_fifty_credits_for_ten_seconds_with_rate_five(): void
    {
        $model = AIModel::factory()->create([
            'cost_per_second_usd' => '0.0700',
            'credits_per_second' => '5.0000',
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'duration_seconds' => 10,
            'active' => true,
        ]);

        $quote = app(GenerationPricingService::class)->quote($model, $preset, 10);

        $this->assertSame(50, $quote->creditsRequired);
    }

    public function test_it_calculates_one_hundred_fifty_credits_for_five_seconds_with_rate_thirty(): void
    {
        $model = AIModel::factory()->create([
            'cost_per_second_usd' => '0.4000',
            'credits_per_second' => '30.0000',
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'duration_seconds' => 5,
            'active' => true,
        ]);

        $quote = app(GenerationPricingService::class)->quote($model, $preset, 5);

        $this->assertSame(150, $quote->creditsRequired);
    }

    public function test_it_calculates_credits_from_model_rate_for_ten_seconds(): void
    {
        $model = AIModel::factory()->create([
            'cost_per_second_usd' => '0.0700',
            'credits_per_second' => '0.2000',
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'duration_seconds' => 10,
            'active' => true,
        ]);

        /** @var GenerationPricingService $service */
        $service = app(GenerationPricingService::class);

        $quote = $service->quote($model, $preset, 10);

        $this->assertSame(2, $quote->creditsRequired);
        $this->assertSame('0.7000', $quote->generationCostUsd);
    }

    public function test_it_fails_when_model_is_inactive(): void
    {
        $model = AIModel::factory()->create([
            'active' => false,
            'public_visible' => true,
            'cost_per_second_usd' => '0.0700',
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'active' => true,
        ]);

        /** @var GenerationPricingService $service */
        $service = app(GenerationPricingService::class);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Selected model is inactive.');

        $service->quote($model, $preset);
    }

    public function test_it_fails_when_model_cost_is_missing(): void
    {
        $model = AIModel::factory()->create([
            'cost_per_second_usd' => null,
            'credits_per_second' => '0.2000',
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'active' => true,
        ]);

        /** @var GenerationPricingService $service */
        $service = app(GenerationPricingService::class);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Selected model does not have a defined generation cost.');

        $service->quote($model, $preset);
    }

    public function test_it_fails_when_model_credits_rate_is_missing(): void
    {
        $model = AIModel::factory()->create([
            'cost_per_second_usd' => '0.0700',
            'credits_per_second' => null,
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'active' => true,
        ]);

        /** @var GenerationPricingService $service */
        $service = app(GenerationPricingService::class);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Selected model does not have a defined credits rate.');

        $service->quote($model, $preset);
    }
}
