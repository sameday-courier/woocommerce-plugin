<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PostMetaHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class WooOpenPackageOrderDataHandler
{
    public static function saveFromSession(int $orderId): void
    {
        if ('yes' !== WooSessionHandler::get(SamedaySessionKeys::OPEN_PACKAGE)) {
            return;
        }

        PostMetaHandler::update(
            $orderId,
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_OPEN_PACKAGE_OPTION,
            1,
            true
        );

        WooSessionHandler::set(SamedaySessionKeys::OPEN_PACKAGE, 'no');
    }

    public static function isEnabled(int $orderId): bool
    {
        return '' !== PostMetaHandler::get(
            $orderId,
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_OPEN_PACKAGE_OPTION,
            true
        );
    }
}
