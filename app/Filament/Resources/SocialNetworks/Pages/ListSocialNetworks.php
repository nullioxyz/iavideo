<?php

namespace App\Filament\Resources\SocialNetworks\Pages;

use App\Filament\Resources\SocialNetworks\SocialNetworksResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSocialNetworks extends ListRecords
{
    protected static string $resource = SocialNetworksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

