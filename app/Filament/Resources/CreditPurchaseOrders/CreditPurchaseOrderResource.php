<?php

namespace App\Filament\Resources\CreditPurchaseOrders;

use App\Domain\Payments\Models\CreditPurchaseOrder;
use App\Filament\Resources\CreditPurchaseOrders\Pages\ListCreditPurchaseOrders;
use App\Filament\Resources\CreditPurchaseOrders\Pages\ViewCreditPurchaseOrder;
use App\Filament\Resources\CreditPurchaseOrders\Schemas\CreditPurchaseOrderInfolist;
use App\Filament\Resources\CreditPurchaseOrders\Tables\CreditPurchaseOrdersTable;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CreditPurchaseOrderResource extends Resource
{
    protected static ?string $model = CreditPurchaseOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Billing';

    public static function infolist(Schema $schema): Schema
    {
        return CreditPurchaseOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CreditPurchaseOrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCreditPurchaseOrders::route('/'),
            'view' => ViewCreditPurchaseOrder::route('/{record}'),
        ];
    }
}
