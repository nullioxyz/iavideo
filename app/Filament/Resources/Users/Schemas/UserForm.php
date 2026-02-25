<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Domain\Auth\Models\Admin;
use App\Domain\Auth\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Spatie\Permission\Models\Role;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->maxLength(72)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('role_names')
                    ->label('Roles')
                    ->multiple()
                    ->options(fn (): array => Role::query()
                        ->where('guard_name', 'api')
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
                    }),
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
    }
}
