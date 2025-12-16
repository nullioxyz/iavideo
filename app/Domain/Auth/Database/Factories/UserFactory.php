<?php

namespace App\Domain\Auth\Database\Factories;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $phone = $this->faker->numerify('###########');

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),

            'username' => $this->faker->unique()->userName(),

            'phone_number' => $phone,
            'phone_number_verified_at' => $phone
                ? $this->faker->dateTimeBetween('-1 year', 'now')
                : null,

            'password' => bcrypt('password'),

            'active' => $this->faker->boolean(95),

            'credit_balance' => $this->faker->numberBetween(0, 10),

            'invited_by_user_id' => null,

            'last_login_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'suspended_at' => null,
            'last_activity_at' => $this->faker->optional()->dateTimeBetween('-7 days', 'now'),

            'user_agent' => $this->faker->optional()->userAgent(),

            'remember_token' => Str::random(10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => false,
            'suspended_at' => now(),
        ]);
    }

    public function noCredits(): static
    {
        return $this->state(fn () => [
            'credit_balance' => 0,
        ]);
    }

    public function withCredits(int $amount = 3): static
    {
        return $this->state(fn () => [
            'credit_balance' => $amount,
        ]);
    }
}
