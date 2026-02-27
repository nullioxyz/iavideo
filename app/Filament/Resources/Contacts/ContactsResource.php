<?php

namespace App\Filament\Resources\Contacts;

use App\Domain\Contacts\Models\Contact;
use App\Filament\Resources\Contacts\Pages\CreateContacts;
use App\Filament\Resources\Contacts\Pages\EditContacts;
use App\Filament\Resources\Contacts\Pages\ListContacts;
use App\Filament\Resources\Contacts\Pages\ViewContacts;
use App\Filament\Resources\Contacts\Schemas\ContactsForm;
use App\Filament\Resources\Contacts\Schemas\ContactsInfolist;
use App\Filament\Resources\Contacts\Tables\ContactsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContactsResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Contacts';

    public static function form(Schema $schema): Schema
    {
        return ContactsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContacts::route('/'),
            'create' => CreateContacts::route('/create'),
            'view' => ViewContacts::route('/{record}'),
            'edit' => EditContacts::route('/{record}/edit'),
        ];
    }
}

