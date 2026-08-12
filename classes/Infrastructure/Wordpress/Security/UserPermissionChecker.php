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
     * @return int
     */
    public static function getCurrentUserId(): int
    {
        return (int) get_current_user_id();
    }

    /**
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        return self::getCurrentUserId() > 0;
    }

    /**
     * @return bool
     */
    public static function hasAllowedRole(): bool
    {
        $roles = wp_get_current_user()->roles ?? [];

        foreach (self::USER_ROLE_PERMISSIONS as $role) {
            if (in_array($role, $roles, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Authenticated user with an allowed admin role.
     *
     * @return bool
     */
    public static function canAccess(): bool
    {
        return self::isLoggedIn() && self::hasAllowedRole();
    }
}
