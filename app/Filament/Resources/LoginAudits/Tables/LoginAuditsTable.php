<?php

namespace App\Filament\Resources\LoginAudits\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoginAuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                IconColumn::make('success')
                    ->label('Success')
                    ->boolean(),
                TextColumn::make('failure_reason')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Login Email')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->searchable(),
                TextColumn::make('country_code')
                    ->label('Country')
                    ->searchable(),
                TextColumn::make('browser')
                    ->searchable(),
                TextColumn::make('platform')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('success')
                    ->options([
                        '1' => 'Success',
                        '0' => 'Failure',
                    ]),
                SelectFilter::make('failure_reason')
                    ->options([
                        'invalid_credentials' => 'Invalid credentials',
                        'inactive_user' => 'Inactive user',
                        'suspended_user' => 'Suspended user',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
