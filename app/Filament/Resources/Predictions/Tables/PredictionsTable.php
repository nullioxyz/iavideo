<?php

namespace App\Filament\Resources\Predictions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PredictionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('input.id')
                    ->searchable(),
                TextColumn::make('model.name')
                    ->searchable(),
                TextColumn::make('external_id')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('source')
                    ->badge(),
                TextColumn::make('attempt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('retry_of_prediction_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('queued_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('failed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('duration_seconds')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('processing_ms')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_ms')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cost_estimate_usd')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cost_actual_usd')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('error_code')
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
