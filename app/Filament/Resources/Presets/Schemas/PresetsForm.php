<?php

namespace App\Filament\Resources\Presets\Schemas;

use App\Domain\Languages\Models\Language;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PresetsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('default_model_id')
                    ->relationship('model', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->required(),

                Textarea::make('prompt')
                    ->label('Prompt')
                    ->required(),

                Textarea::make('negative_prompt')
                    ->label('Negative prompt')
                    ->required(),
                ...self::translationsComponents(),

                Select::make('aspect_ratio')
                    ->label('Aspect Ratio')
                    ->options([
                        '16:9' => '16:9',
                        '9:16' => '9:16',
                        '1:1' => '1:1',
                    ])
                    ->required(),

                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('slug'),
                        Toggle::make('active')
                            ->default(true),
                    ]),

                TextInput::make('duration_seconds')
                    ->label('Duration (seconds)')
                    ->required(),

                TextInput::make('cost_estimate_usd')
                    ->label('Cost Estimate (USD)')
                    ->required(),

                FileUpload::make('preview_image_upload_path')
                    ->label('Preview Image Upload')
                    ->disk('public')
                    ->directory('presets/uploads/images')
                    ->image()
                    ->nullable(),

                Placeholder::make('current_preview_image')
                    ->label('Current Preview Image')
                    ->content(function ($record): HtmlString|string {
                        $url = $record?->previewImageUrl();
                        if (! is_string($url) || $url === '') {
                            return 'No image uploaded';
                        }

                        return new HtmlString('<a href="'.$url.'" target="_blank" rel="noopener noreferrer"><img src="'.$url.'" alt="Preview image" style="max-width:240px;border-radius:8px;" /></a>');
                    }),

                FileUpload::make('preview_video_upload_path')
                    ->label('Preview Video Upload')
                    ->disk('public')
                    ->directory('presets/uploads/videos')
                    ->acceptedFileTypes([
                        'video/mp4',
                        'video/quicktime',
                        'video/webm',
                    ])
                    ->nullable(),

                Placeholder::make('current_preview_video')
                    ->label('Current Preview Video')
                    ->content(function ($record): HtmlString|string {
                        $url = $record?->previewVideoUrl();
                        if (! is_string($url) || $url === '') {
                            return 'No video uploaded';
                        }

                        return new HtmlString('<video controls style="max-width:320px;border-radius:8px;" src="'.$url.'"></video><div><a href="'.$url.'" target="_blank" rel="noopener noreferrer">Open video</a></div>');
                    }),

                TextInput::make('preview_video_url')
                    ->label('Preview Video URL')
                    ->nullable(),

                DateTimePicker::make('created_at'),
                DateTimePicker::make('updated_at'),

                Toggle::make('active')
                    ->required(),
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
                    Textarea::make("translations_payload.{$slug}.prompt")
                        ->label('Translated Prompt')
                        ->nullable(),
                    Textarea::make("translations_payload.{$slug}.negative_prompt")
                        ->label('Translated Negative Prompt')
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
