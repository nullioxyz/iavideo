<?php

namespace App\Domain\Videos\Database\Factories;

use App\Domain\AIModels\Models\Model;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PredictionFactory extends Factory
{
    protected $model = Prediction::class;

    public function definition(): array
    {
        $now = now();

        return [
            'input_id' => Input::factory(),
            'model_id' => Model::factory(),

            'external_id' => (string) Str::uuid(),

            'status' => $this->faker->randomElement(['queued', 'running', 'succeeded', 'failed']),
            'source' => $this->faker->randomElement(['api', 'web', 'worker', 'cron']),
            'attempt' => 1,

            'retry_of_prediction_id' => null,

            'queued_at' => $now->copy()->subSeconds($this->faker->numberBetween(5, 120)),
            'started_at' => null,
            'finished_at' => null,
            'failed_at' => null,

            'cost_estimate_usd' => $this->faker->randomFloat(4, 0, 5),
            'cost_actual_usd' => null,

            'error_code' => null,
            'error_message' => null,

            'request_payload' => [
                'prompt' => $this->faker->sentence(),
                'seed' => $this->faker->numberBetween(1, 999999),
            ],
            'response_payload' => null,

            'created_at' => $now->copy()->subMinutes($this->faker->numberBetween(1, 60)),
            'updated_at' => $now,
        ];
    }

    public function queued(): static
    {
        return $this->state(function () {
            $queuedAt = now()->subSeconds($this->faker->numberBetween(5, 120));

            return [
                'status' => 'queued',
                'queued_at' => $queuedAt,
                'started_at' => null,
                'finished_at' => null,
                'failed_at' => null,
                'cost_actual_usd' => null,
                'error_code' => null,
                'error_message' => null,
                'response_payload' => null,
            ];
        });
    }

    public function running(): static
    {
        return $this->state(function () {
            $queuedAt = now()->subSeconds($this->faker->numberBetween(60, 240));
            $startedAt = (clone $queuedAt)->addSeconds($this->faker->numberBetween(1, 30));

            return [
                'status' => 'running',
                'queued_at' => $queuedAt,
                'started_at' => $startedAt,
                'finished_at' => null,
                'failed_at' => null,
                'cost_actual_usd' => null,
                'error_code' => null,
                'error_message' => null,
                'response_payload' => null,
            ];
        });
    }

    public function succeeded(): static
    {
        return $this->state(function () {
            $queuedAt = now()->subSeconds($this->faker->numberBetween(120, 600));
            $startedAt = (clone $queuedAt)->addSeconds($this->faker->numberBetween(1, 30));
            $finishedAt = (clone $startedAt)->addSeconds($this->faker->numberBetween(5, 120));

            $actual = $this->faker->randomFloat(4, 0, 5);

            return [
                'status' => 'succeeded',
                'queued_at' => $queuedAt,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
                'failed_at' => null,
                'cost_actual_usd' => $actual,
                'error_code' => null,
                'error_message' => null,
                'response_payload' => [
                    'result' => [
                        'url' => $this->faker->url(),
                        'duration_seconds' => $this->faker->numberBetween(1, 300),
                    ],
                    'meta' => [
                        'external_id' => (string) Str::uuid(),
                    ],
                ],
            ];
        });
    }

    public function failed(?string $code = null, ?string $message = null): static
    {
        return $this->state(function () use ($code, $message) {
            $queuedAt = now()->subSeconds($this->faker->numberBetween(120, 600));
            $startedAt = (clone $queuedAt)->addSeconds($this->faker->numberBetween(1, 30));
            $failedAt = (clone $startedAt)->addSeconds($this->faker->numberBetween(5, 120));

            return [
                'status' => 'failed',
                'queued_at' => $queuedAt,
                'started_at' => $startedAt,
                'finished_at' => null,
                'failed_at' => $failedAt,
                'cost_actual_usd' => null,
                'error_code' => $code ?? $this->faker->randomElement(['TIMEOUT', 'PROVIDER_ERROR', 'VALIDATION_ERROR']),
                'error_message' => $message ?? $this->faker->sentence(),
                'response_payload' => [
                    'error' => [
                        'code' => $code ?? 'PROVIDER_ERROR',
                        'message' => $message ?? 'Something went wrong',
                    ],
                ],
            ];
        });
    }

    public function retryOf(Prediction $original, int $attempt = 2): static
    {
        return $this->state(function () use ($original, $attempt) {
            return [
                'retry_of_prediction_id' => $original->getKey(),
                'attempt' => $attempt,
                // normalmente o retry reaproveita input/model do original
                'input_id' => $original->input_id,
                'model_id' => $original->model_id,
            ];
        });
    }
}
