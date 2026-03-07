<?php

namespace App\Domain\Credits\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Credits\Services\GenerationPricingService;
use App\Domain\Settings\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerationPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_credits_from_model_cost_and_duration_with_round_up(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'credit_unit_value_usd'],
            ['value' => '0.35']
        );

        $model = AIModel::factory()->create([
            'cost_per_second_usd' => '0.1500',
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
        $this->assertSame('0.7500', $quote->generationCostUsd);
        $this->assertSame('0.3500', $quote->creditUnitValueUsd);
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
}
