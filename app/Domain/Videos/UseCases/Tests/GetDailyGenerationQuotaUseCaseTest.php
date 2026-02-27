<?php

namespace App\Domain\Videos\UseCases\Tests;

use App\Domain\Auth\Models\User;
use App\Domain\Settings\Models\Setting;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\UseCases\GetDailyGenerationQuotaUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetDailyGenerationQuotaUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_limit_usage_and_near_limit_flags(): void
    {
        Setting::query()->updateOrCreate(['key' => 'max_daily_inputs'], ['value' => '3']);
        Setting::query()->updateOrCreate(['key' => 'daily_input_limit_warning_threshold'], ['value' => '1']);

        $user = User::factory()->create();

        Input::factory()->count(2)->create([
            'user_id' => $user->getKey(),
            'created_at' => now(),
        ]);

        $useCase = new GetDailyGenerationQuotaUseCase;

        $quota = $useCase->execute($user);

        $this->assertSame(3, $quota['daily_limit']);
        $this->assertSame(2, $quota['used_today']);
        $this->assertSame(1, $quota['remaining_today']);
        $this->assertTrue($quota['near_limit']);
        $this->assertFalse($quota['limit_reached']);
    }

    public function test_it_marks_limit_reached_when_user_hits_daily_cap(): void
    {
        Setting::query()->updateOrCreate(['key' => 'max_daily_inputs'], ['value' => '3']);

        $user = User::factory()->create();

        Input::factory()->count(3)->create([
            'user_id' => $user->getKey(),
            'created_at' => now(),
        ]);

        $useCase = new GetDailyGenerationQuotaUseCase;

        $quota = $useCase->execute($user);

        $this->assertTrue($quota['limit_reached']);
        $this->assertSame(0, $quota['remaining_today']);
    }
}
