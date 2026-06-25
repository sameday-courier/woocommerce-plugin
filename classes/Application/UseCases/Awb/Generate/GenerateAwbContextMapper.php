<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Domain\Awb\Generate\GenerateAwbContext;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbContextMapper
{
    public static function fromItem(GenerateAwbItem $item): GenerateAwbContext
    {
        return new GenerateAwbContext(
            $item->getOrderId(),
            $item->getShipping(),
            $item->getBilling(),
            $item->getLocker(),
            $item->hasOpenPackage(),
            $item->hasLockerFirstMile(),
            $item->getPackageType(),
            Helper::parsePostMetaSamedaycourierAddressHd($item->getOrderId()),
        );
    }
}
