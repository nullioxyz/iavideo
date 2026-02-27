<?php

namespace App\Filament\Resources\PresetTags\Schemas;

use App\Domain\Languages\Models\Language;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PresetTagsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Optional. If empty, generated from name.')
                    ->nullable(),
                ...self::translationsComponents(),

                Toggle::make('active')
                    ->default(true)
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
