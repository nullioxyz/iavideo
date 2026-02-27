<?php

namespace App\Domain\SocialNetworks\Controllers;

use App\Domain\SocialNetworks\Models\SocialNetwork;
use App\Domain\SocialNetworks\Resources\SocialNetworkResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SocialNetworkListController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $items = SocialNetwork::query()
            ->where('active', true)
            ->orderBy('id')
            ->get();

        return SocialNetworkResource::collection($items);
    }
}

