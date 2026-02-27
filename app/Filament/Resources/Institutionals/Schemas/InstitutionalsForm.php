<?php

namespace App\Filament\Resources\Institutionals\Schemas;

use App\Domain\Languages\Models\Language;
use App\Filament\Support\FilamentUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class InstitutionalsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            TextInput::make('slug')->required(),
            TextInput::make('subtitle'),
            TextInput::make('short_description'),
            Textarea::make('description')->rows(6),
            FileUpload::make('images_upload_paths')
                ->label('Images Upload')
                ->disk(FilamentUpload::disk())
                ->directory(FilamentUpload::directory('institutionals/uploads'))
                ->multiple()
                ->image()
                ->nullable(),
            ...self::translationsComponents(),
            Toggle::make('active')->default(true),
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

            return Tab::make($label)->schema([
                TextInput::make("translations_payload.{$slug}.title"),
                TextInput::make("translations_payload.{$slug}.slug"),
                TextInput::make("translations_payload.{$slug}.subtitle"),
                TextInput::make("translations_payload.{$slug}.short_description"),
                Textarea::make("translations_payload.{$slug}.description")->rows(4),
            ]);
        })->all();

        return [
            Tabs::make('Translations')->tabs($tabs)->columnSpanFull(),
        ];
    }
}
