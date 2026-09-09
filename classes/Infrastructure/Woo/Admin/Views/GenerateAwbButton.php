<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views;

use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;

final class GenerateAwbButton
{
    /**
     * @param int $orderId
     * @param bool $wrapInField
     *
     * @return string
     */
    public static function render(int $orderId, bool $wrapInField = false): string
    {
        return HtmlHandler::buildHtml('generate-awb-button', [
            'orderId' => $orderId,
            'wrapInField' => $wrapInField,
        ]);
    }
}
