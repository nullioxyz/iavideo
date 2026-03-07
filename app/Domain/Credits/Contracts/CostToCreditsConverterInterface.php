<?php

namespace App\Domain\Credits\Contracts;

interface CostToCreditsConverterInterface
{
    public function convertUsdCostToCredits(string $generationCostUsd): int;

    public function creditUnitValueUsd(): string;
}
