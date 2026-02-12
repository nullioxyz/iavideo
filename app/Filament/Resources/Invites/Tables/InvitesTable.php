<?php

namespace App\Filament\Resources\Invites\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class InvitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('token')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('share_url')
                    ->label('Share link')
                    ->copyable()
                    ->url(fn ($record) => $record->share_url)
                    ->openUrlInNewTab()
                    ->wrap(),
                TextColumn::make('copy_share_link')
                    ->label('Copy link')
                    ->state('Copy')
                    ->badge()
                    ->copyable()
                    ->copyableState(fn ($record) => $record->share_url),
                TextColumn::make('credits_granted')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('invitedBy.name')
                    ->label('Invited by')
                    ->searchable(),
                IconColumn::make('used_at')
                    ->label('Used')
                    ->boolean(fn ($record) => $record->used_at !== null),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
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
                TernaryFilter::make('used_at')
                    ->label('Used')
                    ->nullable(),
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
