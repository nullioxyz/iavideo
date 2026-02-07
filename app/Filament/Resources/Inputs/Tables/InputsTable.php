<?php

namespace App\Filament\Resources\Inputs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InputsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('preset.name')
                    ->label('Preset')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('start_image_path')
                    ->label('Start Image Path'),

                TextColumn::make('original_filename')
                    ->label('Original Filename'),

                TextColumn::make('mime_type')
                    ->label('MIME Type'),

                TextColumn::make('size_bytes')
                    ->label('Size (bytes)'),

                TextColumn::make('credit_debited')
                    ->label('Credit/Debited'),

                TextColumn::make('status')
                    ->label('Status'),

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
