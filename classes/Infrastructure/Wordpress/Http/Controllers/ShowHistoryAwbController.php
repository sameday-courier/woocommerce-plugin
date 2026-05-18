<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

use SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory\ShowHistoryAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory\ShowHistoryAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbHistoryTable;

if (!defined('ABSPATH')) {
    exit;
}

class ShowHistoryAwbController
{
    /**
     * @param int $orderId
     *
     * @return string
     */
    public function render(int $orderId): string
    {
        try {
            $result = (new ShowHistoryAwb(new ShowHistoryAwbRequest($orderId)))->execute();
        } catch (\Exception $exception) {
            return '';
        }

        if (!$result->hasAwb()) {
            return '';
        }

        return AwbHistoryTable::addAwbHistoryTable($result->getPackages());
    }
}
