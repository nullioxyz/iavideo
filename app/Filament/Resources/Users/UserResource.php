<?php

namespace App\Filament\Resources\Users;

use App\Domain\Auth\Models\Admin;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\RelationManagers\InputsRelationManager;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $recordTitleAttribute = 'User';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure(
            schema: $schema,
            allowedRoles: [RoleNames::PLATFORM_USER],
            showRolesField: false,
        );
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InputsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $platformUserIds = User::query()
            ->whereHas('roles', function (Builder $query): void {
                $query->where('name', RoleNames::PLATFORM_USER)
                    ->where('guard_name', 'api');
            })
            ->select('id');

        return parent::getEloquentQuery()
            ->whereIn('id', $platformUserIds);
    }
}
