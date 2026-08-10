<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPackageRepository;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory\ShowHistoryAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory\ShowHistoryAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory\ShowHistoryAwbRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbHistoryTable;

if (!defined('ABSPATH')) {
    exit;
}

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
                    new Sameday(SdkInitiator::init()),
                )
            ))->execute();
        } catch (SamedaySDKException $exception) {
            return '';
        } catch (\Exception $exception) {
            return '';
        }

        if (!$result->hasAwb()) {
            return '';
        }

        return AwbHistoryTable::addAwbHistoryTable($result->getPackages());
    }
}
