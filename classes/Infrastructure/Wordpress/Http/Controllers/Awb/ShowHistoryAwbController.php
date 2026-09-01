<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory\ShowHistoryAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbHistoryTable;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\ShowHistoryAwbFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\ShowHistoryAwbMapper;

final class ShowHistoryAwbController
{
    /**
     * @param int $orderId
     *
     * @return string
     */
    public function render(int $orderId): string
    {
        $params = new ShowHistoryAwbMapper([
            ShowHistoryAwbMapper::ORDER_ID_KEY => $orderId,
        ]);
        $showHistoryAwb = ShowHistoryAwbFactory::create();

        try {
            $result = $showHistoryAwb->execute(
                new ShowHistoryAwbRequest($params->orderId())
            );
        } catch (Exception $exception) {
            return '';
        }

        if (!$result->hasAwb()) {
            return '';
        }

        return AwbHistoryTable::addAwbHistoryTable($result->getPackages());
    }
}
