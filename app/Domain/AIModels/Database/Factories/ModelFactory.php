<?php

namespace App\Domain\AIModels\Database\Factories;

use App\Domain\AIModels\Models\Model;
use App\Domain\Platforms\Models\Platform;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ModelFactory extends Factory
{
    protected $model = Model::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'platform_id' => Platform::factory()->create(),
            'name' => fake()->word(),
            'slug' => fake()->slug(),
            'created_at' => Carbon::now(),
            'active' => true,
        ];
    }
}
