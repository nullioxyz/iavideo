<?php

namespace App\Domain\AIModels\Database\Factories;

use App\Domain\AIModels\Models\PresetTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<PresetTag> */
class PresetTagFactory extends Factory
{
    /** @var class-string<PresetTag> */
    protected $model = PresetTag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'active' => true,
        ];
    }
}

