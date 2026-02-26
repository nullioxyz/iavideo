<?php

namespace App\Domain\Credits\Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\Models\CreditLedger;
use App\Domain\Videos\Models\Input;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CreditLedger> */
class CreditLedgerFactory extends Factory
{
    /** @var class-string<CreditLedger> */
    protected $model = CreditLedger::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'delta' => fake()->url(),
            'balance_after' => 2,
            'reason' => fake()->text(),
            'reference_type' => Input::class,
            'reference_id' => Input::factory(),
            'created_at' => Carbon::now(),
        ];
    }
}
