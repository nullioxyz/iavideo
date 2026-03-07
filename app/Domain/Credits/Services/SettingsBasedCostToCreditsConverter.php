<?php

namespace App\Domain\Credits\Services;

use App\Domain\Credits\Contracts\CostToCreditsConverterInterface;
use App\Domain\Credits\Support\UsdValue;
use App\Domain\Settings\Models\Setting;

final class SettingsBasedCostToCreditsConverter implements CostToCreditsConverterInterface
{
    public function convertUsdCostToCredits(string $generationCostUsd): int
    {
        return max(1, UsdValue::ceilDivide($generationCostUsd, $this->creditUnitValueUsd()));
    }

    public function creditUnitValueUsd(): string
    {
        $configured = Setting::query()
            ->where('key', 'credit_unit_value_usd')
            ->value('value');

        $value = is_string($configured) && trim($configured) !== ''
            ? $configured
            : '0.3500';

        return UsdValue::normalize($value);
    }
}
