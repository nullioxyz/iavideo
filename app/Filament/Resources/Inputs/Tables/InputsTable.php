<?php

namespace App\Filament\Resources\Inputs\Tables;

use App\Domain\Videos\Events\CreatePredictionForInput;
use App\Domain\Videos\Models\Input;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->label('Original Filename')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Input Name')
                    ->searchable(),

                TextColumn::make('mime_type')
                    ->label('MIME Type'),

                TextColumn::make('size_bytes')
                    ->label('Size (bytes)'),

                TextColumn::make('credit_debited')
                    ->label('Credit/Debited'),

                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),

                TextColumn::make('created_at'),
                TextColumn::make('updated_at'),
            ])
            ->filters([
                SelectFilter::make('preset_id')
                    ->label('Preset')
                    ->relationship('preset', 'name')
                    ->searchable()
                    ->preload()
                    ->query(fn (Builder $query, array $data): Builder => self::applyPresetFilter($query, $data['value'] ?? null)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('retry_prediction')
                    ->label('Retry Prediction')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Input $record): bool => in_array($record->status, [Input::FAILED, Input::PROCESSING], true))
                    ->action(function (Input $record): void {
                        try {
                            CreatePredictionForInput::dispatch((int) $record->getKey());

                            Notification::make()
                                ->title('Retry enfileirado')
                                ->body("Novo processamento solicitado para o input #{$record->getKey()}.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Falha ao enfileirar retry')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function applyPresetFilter(Builder $query, mixed $presetId): Builder
    {
        if (! is_numeric($presetId)) {
            return $query;
        }

        return $query->where('preset_id', (int) $presetId);
    }
}
