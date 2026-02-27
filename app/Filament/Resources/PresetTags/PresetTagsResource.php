<?php

namespace App\Filament\Resources\PresetTags;

use App\Domain\AIModels\Models\PresetTag;
use App\Filament\Resources\PresetTags\Pages\CreatePresetTags;
use App\Filament\Resources\PresetTags\Pages\EditPresetTags;
use App\Filament\Resources\PresetTags\Pages\ListPresetTags;
use App\Filament\Resources\PresetTags\Pages\ViewPresetTags;
use App\Filament\Resources\PresetTags\RelationManagers\PresetsRelationManager;
use App\Filament\Resources\PresetTags\Schemas\PresetTagsForm;
use App\Filament\Resources\PresetTags\Schemas\PresetTagsInfolist;
use App\Filament\Resources\PresetTags\Tables\PresetTagsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PresetTagsResource extends Resource
{
    protected static ?string $model = PresetTag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Preset Tags';

    public static function form(Schema $schema): Schema
    {
        return PresetTagsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PresetTagsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PresetTagsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PresetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPresetTags::route('/'),
            'create' => CreatePresetTags::route('/create'),
            'view' => ViewPresetTags::route('/{record}'),
            'edit' => EditPresetTags::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('presets');
    }
}
