<?php

namespace App\Filament\Resources\SocialNetworks\Pages;

use App\Filament\Resources\SocialNetworks\SocialNetworksResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSocialNetworks extends EditRecord
{
    protected static string $resource = SocialNetworksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

