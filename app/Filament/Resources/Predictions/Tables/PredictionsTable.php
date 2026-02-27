<?php

namespace App\Filament\Resources\Predictions\Tables;

use App\Domain\Videos\Models\Prediction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PredictionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('input.id')
                    ->searchable(),
                TextColumn::make('input.title')
                    ->label('Input Name')
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Prediction::QUEUED => Prediction::QUEUED,
                        Prediction::STARTING => Prediction::STARTING,
                        Prediction::SUBMITTING => Prediction::SUBMITTING,
                        Prediction::PROCESSING => Prediction::PROCESSING,
                        Prediction::SUCCEEDED => Prediction::SUCCEEDED,
                        Prediction::FAILED => Prediction::FAILED,
                        Prediction::CANCELLED => Prediction::CANCELLED,
                        Prediction::REFUNDED => Prediction::REFUNDED,
                    ]),
                Filter::make('name_search')
                    ->form([
                        TextInput::make('name')->label('Search Name'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => self::applyNameFilter($query, (string) ($data['name'] ?? ''))),
                self::dateRangeFilter('created_at', 'Created At'),
                self::dateRangeFilter('started_at', 'Started At'),
                self::dateRangeFilter('finished_at', 'Finished At'),
                self::dateRangeFilter('failed_at', 'Failed At'),
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

    private static function dateRangeFilter(string $column, string $label): Filter
    {
        return Filter::make($column.'_range')
            ->label($label)
            ->form([
                DateTimePicker::make('from')->label('From'),
                DateTimePicker::make('until')->label('Until'),
            ])
            ->query(fn (Builder $query, array $data): Builder => self::applyDateRangeFilter($query, $column, $data));
    }

    public static function applyNameFilter(Builder $query, string $name): Builder
    {
        $name = trim($name);

        if ($name === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($name): void {
            $inner->whereHas('input', fn (Builder $inputQuery) => $inputQuery->where('title', 'like', "%{$name}%"))
                ->orWhereHas('model', fn (Builder $modelQuery) => $modelQuery->where('name', 'like', "%{$name}%"));
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function applyDateRangeFilter(Builder $query, string $column, array $data): Builder
    {
        return $query
            ->when(
                filled($data['from'] ?? null),
                fn (Builder $builder): Builder => $builder->where($column, '>=', (string) $data['from'])
            )
            ->when(
                filled($data['until'] ?? null),
                fn (Builder $builder): Builder => $builder->where($column, '<=', (string) $data['until'])
            );
    }
}
