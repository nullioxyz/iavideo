<?php

namespace App\Filament\Resources\Admins;

use App\Domain\Auth\Models\Admin;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Filament\Resources\Admins\Pages\CreateAdmin;
use App\Filament\Resources\Admins\Pages\EditAdmin;
use App\Filament\Resources\Admins\Pages\ListAdmins;
use App\Filament\Resources\Admins\Pages\ViewAdmin;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Admins';

    protected static ?string $recordTitleAttribute = 'Admin';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure(
            schema: $schema,
            allowedRoles: RoleNames::adminPanelRoles(),
            showRolesField: true,
        );
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure(
            table: $table,
            enableRoleFilter: true,
            allowedRoles: RoleNames::adminPanelRoles(),
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'view' => ViewAdmin::route('/{record}'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $adminUserIds = User::query()
            ->whereHas('roles', function (Builder $query): void {
                $query->whereIn('name', RoleNames::adminPanelRoles())
                    ->where('guard_name', 'api');
            })
            ->select('id');

        return parent::getEloquentQuery()
            ->whereIn('id', $adminUserIds);
    }
}
