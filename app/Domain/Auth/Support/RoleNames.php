<?php

namespace App\Domain\Auth\Support;

final class RoleNames
{
    public const ADMIN = 'admin';

    public const DEV = 'dev';

    public const PLATFORM_USER = 'platform_user';

    /**
     * @return list<string>
     */
    public static function adminPanelRoles(): array
    {
        return [
            self::ADMIN,
            self::DEV,
        ];
    }
}

