<?php

namespace App\Filament\Resources\Presets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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

                TextColumn::make('tags')
                    ->label('Tags')
                    ->formatStateUsing(fn ($state, $record) => $record->tags->pluck('name')->implode(', ')),

                TextColumn::make('duration_seconds')
                    ->label('Duration (seconds)'),

                TextColumn::make('cost_estimate_usd')
                    ->label('Legacy Cost Estimate (deprecated)'),

                TextColumn::make('preview_image_url')
                    ->label('Preview Image URL')
                    ->state(fn ($record) => $record->previewImageUrl()),

                TextColumn::make('preview_video_url')
                    ->label('Preview Video URL')
                    ->state(fn ($record) => $record->previewVideoUrl()),

                TextColumn::make('active'),

                TextColumn::make('created_at'),
                TextColumn::make('updated_at'),
            ])
            ->filters([
                SelectFilter::make('aspect_ratio')
                    ->label('Aspect Ratio')
                    ->options([
                        '16:9' => '16:9',
                        '9:16' => '9:16',
                        '1:1' => '1:1',
                    ]),
                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple(),
                SelectFilter::make('duration_seconds')
                    ->label('Duration (seconds)')
                    ->options(fn (): array => \App\Domain\AIModels\Models\Preset::query()
                        ->whereNotNull('duration_seconds')
                        ->orderBy('duration_seconds')
                        ->pluck('duration_seconds', 'duration_seconds')
                        ->mapWithKeys(fn ($duration, $key): array => [(string) $key => (string) $duration])
                        ->all()),
                TernaryFilter::make('active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
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
