<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Security;

if (!defined('ABSPATH')) {
    exit;
}

class UserPermissionChecker
{
    private const USER_ROLE_PERMISSIONS = [
        'administrator',
        'shop_manager',
    ];

    /**
     * @return bool
     */
    public static function hasAllowedRole(): bool
    {
        $roles = wp_get_current_user()->roles ?? [];

        $userRolePermissions = self::USER_ROLE_PERMISSIONS;

        foreach ($userRolePermissions as $role) {
            if (in_array($role, $roles, true)) {
                return true;
            }
        }

        return false;
    }
}
