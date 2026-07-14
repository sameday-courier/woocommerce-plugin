<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services;

use SamedayCourier\Shipping\Domain\AwbPaymentTypes;
use SamedayCourier\Shipping\Domain\PackageTypes;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\TranslatorHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class AwbFormOptionsProvider
{
    /**
     * @return array<int, array{name: string, value: int}>
     */
    public static function getPackageTypeOptions(): array
    {
        $options = [];

        foreach (PackageTypes::getLabelKeys() as $value => $labelKey) {
            $options[] = [
                'name' => TranslatorHandler::translate($labelKey),
                'value' => $value,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array{name: string, value: int}>
     */
    public static function getAwbPaymentTypeOptions(): array
    {
        $options = [];

        foreach (AwbPaymentTypes::getLabelKeys() as $value => $labelKey) {
            $options[] = [
                'name' => TranslatorHandler::translate($labelKey),
                'value' => $value,
            ];
        }

        return $options;
    }
}
