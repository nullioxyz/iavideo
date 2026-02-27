<?php

namespace App\Filament\Resources\Users\Pages;

use App\Domain\Auth\Models\Admin;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['role_names']);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Admin $record */
        $record = $this->record;

        $user = User::query()->find($record->getKey());
        if ($user) {
            $user->syncRoles([RoleNames::PLATFORM_USER]);
        }
    }
}
