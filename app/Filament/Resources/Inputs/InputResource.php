<?php

namespace App\Filament\Resources\Inputs;

use App\Domain\Videos\Models\Input;
use App\Filament\Resources\Inputs\Pages\CreateInput;
use App\Filament\Resources\Inputs\Pages\EditInput;
use App\Filament\Resources\Inputs\Pages\ListInputs;
use App\Filament\Resources\Inputs\Pages\ViewInput;
use App\Filament\Resources\Inputs\Schemas\InputForm;
use App\Filament\Resources\Inputs\Schemas\InputInfolist;
use App\Filament\Resources\Inputs\Tables\InputsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InputResource extends Resource
{
    protected static ?string $model = Input::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return InputForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InputInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InputsTable::configure($table);
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
            'index' => ListInputs::route('/'),
            'create' => CreateInput::route('/create'),
            'view' => ViewInput::route('/{record}'),
            'edit' => EditInput::route('/{record}/edit'),
        ];
    }
}
