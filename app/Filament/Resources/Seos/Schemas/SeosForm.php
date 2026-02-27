<?php

namespace App\Filament\Resources\Seos\Schemas;

use App\Domain\Languages\Models\Language;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class SeosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('slug')->required(),
            TextInput::make('meta_title'),
            TextInput::make('meta_description'),
            TextInput::make('meta_keywords'),
            TextInput::make('canonical_url')->url(),
            TextInput::make('og_title'),
            TextInput::make('og_description'),
            TextInput::make('twitter_title'),
            TextInput::make('twitter_description'),
            FileUpload::make('images_upload_paths')
                ->label('SEO Images Upload')
                ->disk('public')
                ->directory('seo/uploads')
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
                TextInput::make("translations_payload.{$slug}.slug"),
                TextInput::make("translations_payload.{$slug}.meta_title"),
                TextInput::make("translations_payload.{$slug}.meta_description"),
                TextInput::make("translations_payload.{$slug}.meta_keywords"),
                TextInput::make("translations_payload.{$slug}.og_title"),
                TextInput::make("translations_payload.{$slug}.og_description"),
                TextInput::make("translations_payload.{$slug}.twitter_title"),
                TextInput::make("translations_payload.{$slug}.twitter_description"),
            ]);
        })->all();

        return [
            Tabs::make('Translations')->tabs($tabs)->columnSpanFull(),
        ];
    }
}

