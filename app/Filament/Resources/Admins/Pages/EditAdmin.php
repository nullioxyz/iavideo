<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Domain\Auth\Models\Admin;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Filament\Resources\Admins\AdminResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['role_names']);

        return $data;
    }

    protected function afterSave(): void
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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
