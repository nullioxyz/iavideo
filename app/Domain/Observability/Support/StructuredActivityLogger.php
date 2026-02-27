<?php

namespace App\Domain\Observability\Support;

use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\Log;

class StructuredActivityLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function log(string $action, ?User $user = null, array $context = []): void
    {
        Log::info('activity.'.$action, array_merge([
            'action' => $action,
            'user_id' => $user?->getKey(),
            'email' => $user?->email,
            'at' => now()->toISOString(),
        ], $context));
    }
}

