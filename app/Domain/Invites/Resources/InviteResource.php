<?php

namespace App\Domain\Invites\Resources;

use App\Domain\Invites\Models\Invite;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Invite */
class InviteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'token' => $this->token,
            'share_url' => $this->share_url,
            'credits_granted' => (int) $this->credits_granted,
            'invited_by_user_id' => $this->invited_by_user_id,
            'used_at' => optional($this->used_at)?->toISOString(),
            'expires_at' => optional($this->expires_at)?->toISOString(),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
