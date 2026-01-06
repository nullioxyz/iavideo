<?php

namespace App\Filament\Resources\PredictionOutputs;

use App\Domain\Videos\Models\PredictionOutput;
use App\Filament\Resources\PredictionOutputs\Pages\CreatePredictionOutput;
use App\Filament\Resources\PredictionOutputs\Pages\EditPredictionOutput;
use App\Filament\Resources\PredictionOutputs\Pages\ListPredictionOutputs;
use App\Filament\Resources\PredictionOutputs\Pages\ViewPredictionOutput;
use App\Filament\Resources\PredictionOutputs\Schemas\PredictionOutputForm;
use App\Filament\Resources\PredictionOutputs\Schemas\PredictionOutputInfolist;
use App\Filament\Resources\PredictionOutputs\Tables\PredictionOutputsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PredictionOutputResource extends Resource
{
    protected static ?string $model = PredictionOutput::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PredictionOutputForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PredictionOutputInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PredictionOutputsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPredictionOutputs::route('/'),
            'create' => CreatePredictionOutput::route('/create'),
            'view' => ViewPredictionOutput::route('/{record}'),
            'edit' => EditPredictionOutput::route('/{record}/edit'),
        ];
    }
}
