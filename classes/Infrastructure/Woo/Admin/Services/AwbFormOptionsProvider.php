<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Services;

use SamedayCourier\Shipping\Domain\CarrierAwbPaymentTypes;
use SamedayCourier\Shipping\Domain\CarrierPackageTypes;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

final class AwbFormOptionsProvider
{
    /**
     * @return array<int, array{name: string, value: int}>
     */
    public static function getPackageTypeOptions(): array
    {
        $options = [];

        foreach (CarrierPackageTypes::getLabelKeys() as $value => $labelKey) {
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

        foreach (CarrierAwbPaymentTypes::getLabelKeys() as $value => $labelKey) {
            $options[] = [
                'name' => TranslatorHandler::translate($labelKey),
                'value' => $value,
            ];
        }

        return $options;
    }
}
