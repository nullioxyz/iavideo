<?php

namespace App\Domain\SocialNetworks\Resources;

use App\Domain\SocialNetworks\Models\SocialNetwork;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SocialNetwork */
class SocialNetworkResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'slug' => $this->slug,
            'active' => (bool) $this->active,
        ];
    }
}

