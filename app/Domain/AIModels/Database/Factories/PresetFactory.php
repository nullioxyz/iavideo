<?php

namespace App\Domain\AIModels\Database\Factories;

use App\Domain\AIModels\Models\Model;
use App\Domain\AIModels\Models\Preset;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PresetFactory extends Factory
{
    protected $model = Preset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'default_model_id' => Model::factory()->create(),
            'name' => fake()->word(),
            'prompt' => fake()->text(),
            'negative_prompt' => fake()->text(),
            'aspect_ratio' => '16:9',
            'duration_seconds' => 5,
            'cost_estimate_usd' => 0.7,
            'preview_video_url' => null,
            'active' => true,
            'created_at' => Carbon::now(),
            'active' => true,
        ];
    }
}
