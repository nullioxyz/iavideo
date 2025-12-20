<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginatedResource extends JsonResource
{
    public function __construct(
        private readonly LengthAwarePaginator $paginator,
        private readonly string $resourceClass
    ) {
        parent::__construct($paginator);
    }

    public function toArray($request): array
    {
        $resourceClass = $this->resourceClass;

        return [
            'data' => $resourceClass::collection($this->paginator->items()),
            'meta' => [
                'current_page' => $this->paginator->currentPage(),
                'from' => $this->paginator->firstItem(),
                'to' => $this->paginator->lastItem(),
                'per_page' => $this->paginator->perPage(),
                'total' => $this->paginator->total(),
                'last_page' => $this->paginator->lastPage(),
            ],
            'links' => [
                'first' => $this->paginator->url(1),
                'last' => $this->paginator->url($this->paginator->lastPage()),
                'prev' => $this->paginator->previousPageUrl(),
                'next' => $this->paginator->nextPageUrl(),
            ],
        ];
    }
}
