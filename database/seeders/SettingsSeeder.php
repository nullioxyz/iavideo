<?php

namespace Database\Seeders;

use App\Domain\Settings\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'max_daily_inputs' => '3',
            'daily_input_limit_warning_threshold' => '1',
            'credit_unit_value_usd' => '0.35',
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
