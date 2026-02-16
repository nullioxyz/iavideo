<?php

namespace App\Domain\Videos\Database\Factories;

use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Videos\Models\Input;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Input> */
class InputFactory extends Factory
{
    /** @var class-string<Input> */
    protected $model = Input::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'preset_id' => Preset::factory(),
            'start_image_path' => fake()->url(),
            'original_filename' => 'fakefilename.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 10, // 2KB
            'credit_debited' => 1,
            'credit_ledger_id' => null,
            'status' => 'created',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
