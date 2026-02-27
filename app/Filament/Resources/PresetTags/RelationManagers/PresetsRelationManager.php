<?php

namespace App\Filament\Resources\PresetTags\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PresetsRelationManager extends RelationManager
{
    protected static string $relationship = 'presets';

    protected static ?string $title = 'Presets';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable(),
                IconColumn::make('active')->boolean()->sortable(),
                TextColumn::make('aspect_ratio')->sortable(),
                TextColumn::make('duration_seconds')->numeric()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordUrl(fn ($record): string => route('filament.admin.resources.presets.view', ['record' => $record]));
    }
}
