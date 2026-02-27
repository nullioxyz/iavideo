<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Auth\Models\User;
use App\Domain\Settings\Models\Setting;
use App\Domain\Videos\Models\Input;

class GetDailyGenerationQuotaUseCase
{
    /**
     * @return array{daily_limit:int,used_today:int,remaining_today:int,near_limit:bool,limit_reached:bool}
     */
    public function execute(User $user): array
    {
        $maxDailyInputs = $this->settingInt('max_daily_inputs', 50);
        $warningThreshold = max(1, $this->settingInt('daily_input_limit_warning_threshold', 1));

        $todayInputsCount = Input::query()
            ->where('user_id', $user->getKey())
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $remaining = max(0, $maxDailyInputs - $todayInputsCount);

        return [
            'daily_limit' => $maxDailyInputs,
            'used_today' => $todayInputsCount,
            'remaining_today' => $remaining,
            'near_limit' => $remaining <= $warningThreshold,
            'limit_reached' => $todayInputsCount >= $maxDailyInputs,
        ];
    }

    private function settingInt(string $key, int $default): int
    {
        $value = Setting::query()->where('key', $key)->value('value');

        return is_numeric($value) ? (int) $value : $default;
    }
}
