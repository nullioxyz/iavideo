<?php

namespace App\Domain\AIModels\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AIModelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'platform_id' => $this->platform_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'version' => $this->version,
            'active' => (bool) $this->active,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
