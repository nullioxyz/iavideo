<?php

namespace App\Domain\Platforms\Database\Factories;

use App\Domain\Platforms\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/** @extends Factory<Platform> */
class PlatformFactory extends Factory
{
    /** @var class-string<Platform> */
    protected $model = Platform::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => fake()->slug(),
            'created_at' => Carbon::now(),
        ];
    }
}
