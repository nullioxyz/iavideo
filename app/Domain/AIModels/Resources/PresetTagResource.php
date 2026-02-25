<?php

namespace App\Domain\AIModels\Resources;

use App\Domain\AIModels\Models\PresetTag;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PresetTag */
class PresetTagResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
