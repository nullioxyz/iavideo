<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('username')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->searchable(),
                TextColumn::make('phone_number_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('roles')
                    ->label('Roles')
                    ->state(function ($record): string {
                        $user = \App\Domain\Auth\Models\User::query()->find($record->getKey());

                        return $user?->getRoleNames()->implode(', ') ?? '-';
                    }),
                IconColumn::make('must_reset_password')
                    ->label('Must reset password')
                    ->boolean(),
                IconColumn::make('active')
                    ->boolean(),
                TextColumn::make('credit_balance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('invitedBy.name')
                    ->label('Invited by')
                    ->searchable(),
                TextColumn::make('invites_sent_count')
                    ->label('Invites sent')
                    ->counts('invitesSent')
                    ->sortable(),
                TextColumn::make('last_login_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('suspended_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user_agent')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
