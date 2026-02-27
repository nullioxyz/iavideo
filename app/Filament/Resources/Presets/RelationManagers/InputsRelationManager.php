<?php

namespace App\Filament\Resources\Presets\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InputsRelationManager extends RelationManager
{
    protected static string $relationship = 'inputs';

    protected static ?string $title = 'Generated Inputs';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('title')->label('Input Name')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('user.name')->label('User')->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordUrl(fn ($record): string => route('filament.admin.resources.inputs.view', ['record' => $record]));
    }
}
