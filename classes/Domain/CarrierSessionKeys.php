<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

final class CarrierSessionKeys
{
    public const OPEN_PACKAGE = 'open_package';
    public const LOCKER = 'locker';
    public const PAYMENT_METHOD = 'payment_method';
    public const CHOSEN_PAYMENT_METHOD = 'chosen_payment_method';
    public const CHOSEN_SHIPPING_METHODS = 'chosen_shipping_methods';
    public const SHIPPING_FOR_PACKAGE_PREFIX = 'shipping_for_package_';

    /**
     * @param int $packageKey
     *
     * @return string
     */
    public static function shippingForPackage(int $packageKey): string
    {
        return self::SHIPPING_FOR_PACKAGE_PREFIX . $packageKey;
    }
}
