<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Domain\Auth\Models\Admin;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Filament\Resources\Admins\AdminResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['role_names']);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Admin $record */
        $record = $this->record;

        $roleNames = collect($this->data['role_names'] ?? [])
            ->filter(fn ($role) => is_string($role) && in_array($role, RoleNames::adminPanelRoles(), true))
            ->values()
            ->all();

        if ($roleNames === []) {
            $roleNames = [RoleNames::ADMIN];
        }

        $user = User::query()->find($record->getKey());
        if ($user) {
            $user->syncRoles($roleNames);
        }
    }
}
