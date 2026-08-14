<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPackageRepository;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory\ShowHistoryAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory\ShowHistoryAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory\ShowHistoryAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbHistoryTable;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProviderService;

final class ShowHistoryAwbController
{
    /**
     * @param int $orderId
     *
     * @return string
     */
    public function render(int $orderId): string
    {
        try {
            $result = (new ShowHistoryAwb(
                new ShowHistoryAwbRequest(
                    ShowHistoryAwbItem::fromArray(['order-id' => $orderId]),
                    new SamedayAwbRepository(),
                    new SamedayPackageRepository(),
                    new CourierServiceProviderService(),
                )
            ))->execute();
        } catch (Exception $exception) {
            return '';
        }

        if (!$result->hasAwb()) {
            return '';
        }

        return AwbHistoryTable::addAwbHistoryTable($result->getPackages());
    }
}
