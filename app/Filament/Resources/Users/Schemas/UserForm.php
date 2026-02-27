<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Domain\Auth\Models\Admin;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    /**
     * @param  list<string>|null  $allowedRoles
     */
    public static function configure(Schema $schema, ?array $allowedRoles = null, bool $showRolesField = true): Schema
    {
        $allowedRoles ??= [RoleNames::PLATFORM_USER, ...RoleNames::adminPanelRoles()];

        $components = [
            TextInput::make('name')
                ->required(),
            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required(),
            DateTimePicker::make('email_verified_at'),
            TextInput::make('username')
                ->required(),
            TextInput::make('phone_number')
                ->tel()
                ->required(),
            DateTimePicker::make('phone_number_verified_at')
                ->required(),
            Select::make('language_id')
                ->label('Language')
                ->relationship('language', 'title')
                ->searchable()
                ->preload(),
            TextInput::make('country_code')
                ->label('Country Code')
                ->length(2)
                ->maxLength(2)
                ->placeholder('BR')
                ->nullable(),
            TextInput::make('password')
                ->password()
                ->revealable()
                ->minLength(8)
                ->maxLength(72)
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state)),
        ];

        if ($showRolesField) {
            $components[] = Select::make('role_names')
                ->label('Roles')
                ->multiple()
                ->options(fn (): array => Role::query()
                    ->where('guard_name', 'api')
                    ->whereIn('name', $allowedRoles)
                    ->orderBy('name')
                    ->pluck('name', 'name')
                    ->all())
                ->afterStateHydrated(function (Select $component, ?Admin $record): void {
                    if (! $record) {
                        return;
                    }

                    $user = User::query()->find($record->getKey());

                    $component->state(
                        $user?->getRoleNames()->values()->all() ?? []
                    );
                });
        }

        $components = array_merge($components, [
            Toggle::make('must_reset_password')
                ->label('Force password reset on next login'),
            Toggle::make('active')
                ->required(),
            TextInput::make('credit_balance')
                ->required()
                ->numeric(),
            Select::make('invited_by_user_id')
                ->label('Invited by')
                ->relationship('invitedBy', 'name')
                ->searchable()
                ->preload(),
            DateTimePicker::make('last_login_at'),
            DateTimePicker::make('suspended_at'),
            DateTimePicker::make('last_activity_at'),
            TextInput::make('user_agent'),
        ]);

        return $schema
            ->components($components);
    }
}
