<?php

namespace App\Domain\Credits\Resources;

use App\Domain\Auth\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class CreditBalanceResource extends JsonResource
{
    public function __construct(
        User $resource,
        private readonly int $balance,
    ) {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'user_id' => $this->id,
            'credit_balance' => $this->balance,
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
