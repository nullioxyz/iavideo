<?php

namespace App\Filament\Resources\Models\Schemas;

use App\Domain\Languages\Models\Language;
use Filament\Forms\Get;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ModelsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('platform_id')
                    ->relationship('platform', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                TextInput::make('provider_model_key')
                    ->label('Provider / Model Key')
                    ->required(),
                TextInput::make('version')
                    ->label('Version')
                    ->nullable(),
                TextInput::make('cost_per_second_usd')
                    ->label('Cost / Second (USD)')
                    ->numeric()
                    ->minValue(0.0001)
                    ->required(fn (Get $get): bool => (bool) $get('active') || (bool) $get('public_visible'))
                    ->nullable(),
                TextInput::make('credits_per_second')
                    ->label('Credits / Second')
                    ->numeric()
                    ->minValue(0.0001)
                    ->required(fn (Get $get): bool => (bool) $get('active') || (bool) $get('public_visible'))
                    ->helperText('Used to calculate user charges. Active/public models must define this value.')
                    ->nullable(),
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                ...self::translationsComponents(),

                Toggle::make('active')
                    ->required(),
                Toggle::make('public_visible')
                    ->label('Visible to Users')
                    ->required(),
                Toggle::make('default')
                    ->label('Default')
                    ->default(false)
                    ->required(),

                DateTimePicker::make('created_at'),
                DateTimePicker::make('updated_at'),
            ]);
    }

    private static function translationsComponents(): array
    {
        $languages = Language::query()
            ->where('active', true)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        if ($languages->isEmpty()) {
            return [];
        }

        $tabs = $languages->map(static function (Language $language): Tab {
            $slug = (string) $language->slug;
            $label = (string) $language->title;
            if ($language->is_default) {
                $label .= ' (default)';
            }

            return Tab::make($label)
                ->schema([
                    TextInput::make("translations_payload.{$slug}.name")
                        ->label('Translated Name')
                        ->maxLength(255)
                        ->nullable(),
                    TextInput::make("translations_payload.{$slug}.slug")
                        ->label('Translated Slug')
                        ->maxLength(255)
                        ->nullable(),
                ]);
        })->all();

        return [
            Tabs::make('Translations')
                ->tabs($tabs)
                ->columnSpanFull(),
        ];
    }
}
