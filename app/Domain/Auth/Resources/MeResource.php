<?php

namespace App\Domain\Auth\Resources;

use App\Domain\Auth\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class MeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'phone_number' => $this->phone_number,
            'country_code' => $this->country_code,
            'language' => [
                'id' => $this->language?->id,
                'title' => $this->language?->title,
                'slug' => $this->language?->slug,
            ],
            'active' => (bool) $this->active,
            'credit_balance' => (int) $this->credit_balance,
            'roles' => $this->getRoleNames()->values()->all(),
            'can_access_admin' => $this->canAccessAdminPanel(),
            'must_reset_password' => (bool) $this->must_reset_password,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
