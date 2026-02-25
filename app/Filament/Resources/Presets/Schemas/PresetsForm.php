<?php

namespace App\Filament\Resources\Presets\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
}
