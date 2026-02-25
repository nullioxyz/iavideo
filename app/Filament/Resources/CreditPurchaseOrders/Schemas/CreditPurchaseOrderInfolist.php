<?php

namespace App\Filament\Resources\CreditPurchaseOrders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CreditPurchaseOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id'),
                TextEntry::make('user.email')->label('User'),
                TextEntry::make('provider')->badge(),
                TextEntry::make('external_id'),
                TextEntry::make('status')->badge(),
                TextEntry::make('credits')->numeric(),
                TextEntry::make('amount_cents')->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('checkout_url')->url(fn ($state) => (string) $state)->openUrlInNewTab(),
                TextEntry::make('failure_code'),
                TextEntry::make('failure_message'),
                TextEntry::make('paid_at')->dateTime(),
                TextEntry::make('failed_at')->dateTime(),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }
}
