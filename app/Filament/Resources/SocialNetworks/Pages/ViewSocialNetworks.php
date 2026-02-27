<?php

namespace App\Filament\Resources\SocialNetworks\Pages;

use App\Filament\Resources\SocialNetworks\SocialNetworksResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSocialNetworks extends ViewRecord
{
    protected static string $resource = SocialNetworksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

