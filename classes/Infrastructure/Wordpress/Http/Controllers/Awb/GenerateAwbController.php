<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use JsonException;
use Sameday\Exceptions\SamedaySDKException;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WcHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined("ABSPATH")) {
    exit;
}

final class GenerateAwbController extends AbstractController
{
    private const ACTION = "add-awb";

    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array $inputParams
     *
     * @return void
     *
     * @throws JsonException
     * @throws SamedaySDKException
     */
    protected function processPostAction(array $inputParams): void
    {
        $orderId = (int) $inputParams['samedaycourier-order-id'];
        $orderData = WcHandler::getShippingOrderById($orderId);

        if (empty($orderData)) {
            Redirector::to('index.php');
        }

        $data = array_merge($inputParams, $orderData);

        $dbHandler = new DbHandler();
        $generateAwb = new GenerateAwb(
            new GenerateAwbRequest(
                GenerateAwbItem::fromArray($data),
                $dbHandler,
                new SamedayServiceRepository($dbHandler),
                new SamedayAwbRepository($dbHandler),
            )
        );

        $result = $generateAwb->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
            );
        }

        Redirector::to(
            'post.php',
            [
                'post' => $orderId,
                'action' => 'edit',
                'add-awb' => $result->getNoticeType(),
            ]
        );
    }
}
