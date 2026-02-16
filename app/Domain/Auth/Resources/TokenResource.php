<?php

namespace App\Domain\Auth\Resources;

use App\Domain\Auth\DTO\TokenDTO;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var TokenDTO $token */
        $token = $this->resource;

        return [
            'access_token' => $token->accessToken,
            'token_type' => $token->tokenType,
            'expires_in' => $token->expiresInSeconds,
        ];
    }
}
