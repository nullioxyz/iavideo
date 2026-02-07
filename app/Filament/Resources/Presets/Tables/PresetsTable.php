<?php

namespace App\Filament\Resources\Presets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PresetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('model.name')
                    ->label('Model')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('aspect_ratio')
                    ->label('Aspect Ratio'),

                TextColumn::make('duration_seconds')
                    ->label('Duration (seconds)'),

                TextColumn::make('cost_estimate_usd')
                    ->label('Cost Estimate (USD)'),

                TextColumn::make('preview_video_url')
                    ->label('Preview Video URL'),

                TextColumn::make('active'),

                TextColumn::make('created_at'),
                TextColumn::make('updated_at'),
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
