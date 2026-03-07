<?php

namespace App\Domain\Credits\Support;

use InvalidArgumentException;

final class UsdValue
{
    private const SCALE = 4;

    private const MULTIPLIER = 10000;

    public static function normalize(string|int|float|null $value): string
    {
        return self::fromScaledInt(self::toScaledInt($value));
    }

    public static function toScaledInt(string|int|float|null $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return 0;
        }

        $negative = str_starts_with($normalized, '-');
        $normalized = ltrim($normalized, '+-');

        if (! preg_match('/^\d+(?:\.\d+)?$/', $normalized)) {
            throw new InvalidArgumentException("Invalid USD decimal value [{$value}].");
        }

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '');
        $fraction = substr(str_pad($fraction, self::SCALE, '0'), 0, self::SCALE);

        $scaled = ((int) $whole * self::MULTIPLIER) + (int) $fraction;

        return $negative ? -$scaled : $scaled;
    }

    public static function fromScaledInt(int $scaled): string
    {
        $negative = $scaled < 0;
        $scaled = abs($scaled);

        $whole = intdiv($scaled, self::MULTIPLIER);
        $fraction = str_pad((string) ($scaled % self::MULTIPLIER), self::SCALE, '0', STR_PAD_LEFT);

        return ($negative ? '-' : '').$whole.'.'.$fraction;
    }

    public static function multiplyByInteger(string|int|float|null $value, int $multiplier): string
    {
        return self::fromScaledInt(self::toScaledInt($value) * $multiplier);
    }

    public static function ceilDivide(string|int|float|null $numerator, string|int|float|null $denominator): int
    {
        $numeratorScaled = self::toScaledInt($numerator);
        $denominatorScaled = self::toScaledInt($denominator);

        if ($denominatorScaled <= 0) {
            throw new InvalidArgumentException('USD denominator must be greater than zero.');
        }

        if ($numeratorScaled <= 0) {
            return 0;
        }

        return (int) ceil($numeratorScaled / $denominatorScaled);
    }
}
