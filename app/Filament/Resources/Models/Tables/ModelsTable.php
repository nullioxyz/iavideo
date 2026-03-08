<?php

namespace App\Filament\Resources\Models\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('platform.name')
                    ->label('Platform')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug'),
                TextColumn::make('provider_model_key')
                    ->label('Provider / Model Key')
                    ->searchable(),
                TextColumn::make('version')
                    ->label('Version'),
                TextColumn::make('cost_per_second_usd')
                    ->label('Cost / Second (USD)'),
                TextColumn::make('credits_per_second')
                    ->label('Credits / Second'),
                TextColumn::make('active')
                    ->label('active'),
                TextColumn::make('public_visible')
                    ->label('Visible'),
                TextColumn::make('sort_order')
                    ->label('Sort'),
                TextColumn::make('created_at')->dateTime(),
                TextColumn::make('updated_at')->dateTime(),
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
