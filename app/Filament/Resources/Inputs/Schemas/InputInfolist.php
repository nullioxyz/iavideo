<?php

namespace App\Filament\Resources\Inputs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;

class InputInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('preset.name')
                    ->label('Preset'),

                TextEntry::make('user.name')
                    ->label('User'),

                TextEntry::make('user.name')
                    ->label('User'),

                ImageColumn::make('start_image')
                    ->getStateUsing(fn ($record) => $record->getFirstMediaUrl('start_image'))
                    ->label('Start image'),

                TextEntry::make('start_image_path')
                    ->label('Start Image Path'),

                TextEntry::make('original_filename')
                    ->label('Original Filename'),

                TextEntry::make('mime_type')
                    ->label('MIME Type'),

                TextEntry::make('size_bytes')
                    ->label('Size (bytes)'),

                TextEntry::make('credit_debited')
                    ->label('Credit/Debited'),

                TextEntry::make('status')
                    ->label('Status'),

                TextEntry::make('created_at'),
                TextEntry::make('updated_at'),
            ]);
    }
}
