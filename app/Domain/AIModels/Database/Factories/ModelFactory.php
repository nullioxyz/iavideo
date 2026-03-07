<?php

namespace App\Domain\AIModels\Database\Factories;

use App\Domain\AIModels\Models\Model;
use App\Domain\Platforms\Models\Platform;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Model> */
class ModelFactory extends Factory
{
    /** @var class-string<Model> */
    protected $model = Model::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providerModelKey = fake()->unique()->slug();

        return [
            'platform_id' => Platform::factory(),
            'name' => fake()->word(),
            'slug' => $providerModelKey,
            'provider_model_key' => $providerModelKey,
            'version' => null,
            'cost_per_second_usd' => '0.0700',
            'public_visible' => true,
            'sort_order' => 0,
            'created_at' => Carbon::now(),
            'active' => true,
        ];
    }
}
