<?php

namespace App\Domain\Videos\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class AspectRatio implements ValidationRule
{
    public function __construct(
        private readonly int $w,
        private readonly int $h,
        private readonly float $tolerancePercent = 3.0
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $path = $value->getRealPath();
        if (! $path) {
            return;
        }

        $size = @getimagesize($path);
        if (! $size || ! isset($size[0], $size[1])) {
            return;
        }

        [$width, $height] = $size;

        if ($width <= 0 || $height <= 0) {
            return;
        }

        $expected = $this->w / $this->h;
        $actual = $width / $height;

        $tolerance = $this->tolerancePercent / 100.0;
        $min = $expected * (1.0 - $tolerance);
        $max = $expected * (1.0 + $tolerance);

        if ($actual < $min || $actual > $max) {
            $fail(__('validation.custom.image.aspect_ratio_9_16'));
        }
    }
}
