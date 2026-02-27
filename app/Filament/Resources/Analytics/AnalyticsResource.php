<?php

namespace App\Filament\Resources\Analytics;

use App\Domain\Analytics\Models\Analytics;
use App\Filament\Resources\Analytics\Pages\CreateAnalytics;
use App\Filament\Resources\Analytics\Pages\EditAnalytics;
use App\Filament\Resources\Analytics\Pages\ListAnalytics;
use App\Filament\Resources\Analytics\Pages\ViewAnalytics;
use App\Filament\Resources\Analytics\Schemas\AnalyticsForm;
use App\Filament\Resources\Analytics\Schemas\AnalyticsInfolist;
use App\Filament\Resources\Analytics\Tables\AnalyticsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnalyticsResource extends Resource
{
    protected static ?string $model = Analytics::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Analytics';

    public static function form(Schema $schema): Schema
    {
        return AnalyticsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AnalyticsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnalyticsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnalytics::route('/'),
            'create' => CreateAnalytics::route('/create'),
            'view' => ViewAnalytics::route('/{record}'),
            'edit' => EditAnalytics::route('/{record}/edit'),
        ];
    }
}

