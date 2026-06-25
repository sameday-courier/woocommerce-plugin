<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate\Resolvers;

use JsonException;
use SamedayCourier\Shipping\Domain\Awb\Generate\AwbOohDelivery;
use SamedayCourier\Shipping\Domain\Awb\Generate\GenerateAwbContext;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbOohDeliveryResolver
{
    /**
     * @throws JsonException
     */
    public function resolve(GenerateAwbContext $context, SamedayService $service): AwbOohDelivery
    {
        $lockerId = null;
        $oohLastMile = null;

        if (null === ($locker = $context->getLocker())
            || !Helper::isOohDeliveryOption($service->getSamedayCode())
        ) {
            return new AwbOohDelivery();
        }

        $locker = json_decode(
            $locker,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if ($service->getSamedayCode() === SamedayConstants::LOCKER_NEXT_DAY_CODE) {
            $lockerId = $locker['id'] ?? $locker['lockerId'];
        }

        if ($service->getSamedayCode() === SamedayConstants::PUDO_CODE) {
            $oohLastMile = $locker['id'] ?? $locker['lockerId'];
        }

        return new AwbOohDelivery($lockerId, $oohLastMile);
    }
}
