<?php

namespace App\Domain\Videos\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InputCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly int $inputId,
        public readonly string $tempPath // path relativo dentro do storage/app
    ) {}

}
