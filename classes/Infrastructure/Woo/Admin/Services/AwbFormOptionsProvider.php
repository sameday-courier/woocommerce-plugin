<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services;

use SamedayCourier\Shipping\Domain\SamedayAwbPaymentTypes;
use SamedayCourier\Shipping\Domain\SamedayPackageTypes;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;

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

        foreach (SamedayPackageTypes::getLabelKeys() as $value => $labelKey) {
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

        foreach (SamedayAwbPaymentTypes::getLabelKeys() as $value => $labelKey) {
            $options[] = [
                'name' => TranslatorHandler::translate($labelKey),
                'value' => $value,
            ];
        }

        return $options;
    }
}
