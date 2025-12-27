<?php

namespace App\Domain\Videos\Database\Factories;

use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PredictionOutputFactory extends Factory
{
    protected $model = PredictionOutput::class;

    public function definition(): array
    {
        $now = now();

        return [
            'prediction_id' => Prediction::factory(),
            'kind' => 'video',

            'path' => (string) Str::uuid(),

            'created_at' => $now->copy()->subMinutes($this->faker->numberBetween(1, 60)),
            'updated_at' => $now,
        ];
    }
}
