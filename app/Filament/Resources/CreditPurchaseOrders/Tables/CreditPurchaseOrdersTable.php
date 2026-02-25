<?php

namespace App\Filament\Resources\CreditPurchaseOrders\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CreditPurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.email')->label('User')->searchable(),
                TextColumn::make('provider')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('credits')->numeric()->sortable(),
                TextColumn::make('amount_cents')->numeric()->sortable(),
                TextColumn::make('currency')->searchable(),
                TextColumn::make('paid_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
