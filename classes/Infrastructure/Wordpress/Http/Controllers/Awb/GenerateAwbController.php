<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Woo\WooOrderAddressRepository;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressArchive;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSamedayShippingHdAddressParser;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined("ABSPATH")) {
    exit;
}

final class GenerateAwbController extends AbstractController
{
    private const ACTION = "add-awb";

    /**
     * @return string
     */
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
    protected function processAction(array $inputParams): void
    {
        $orderId = (int) $inputParams['samedaycourier-order-id'];
        $orderData = WooShippingHandler::getShippingOrderById($orderId);

        if (empty($orderData)) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate("There is no data to process."),
                ResponseNoticeType::ERROR,
            );

            Redirector::to(
                'post.php',
                [
                    'id' => $orderId,
                    'post' => $orderId,
                    'action' => 'edit',
                ]
            );
        }

        $data = array_merge($inputParams, $orderData);

        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
                ResponseNoticeType::ERROR,
            );

            Redirector::to(
                'post.php',
                [
                    'post' => $orderId,
                    'action' => 'edit',
                    'add-awb' => ResponseNoticeType::ERROR,
                ]
            );
        }

        $dbHandler = new DbHandler();
        $generateAwb = new GenerateAwb(
            new GenerateAwbRequest(
                GenerateAwbItem::fromArray($data),
                $samedayApiClient,
                $dbHandler,
                new SamedayServiceRepository($dbHandler),
                new SamedayAwbRepository($dbHandler),
                new WooOrderShippingAddressUpdater(
                    new WooOrderAddressRepository($dbHandler),
                    new WooOrderShippingAddressArchive(),
                    new SamedayLockerRepository($dbHandler),
                    new WooSamedayShippingHdAddressParser(),
                ),
                new AwbErrorParser(),
            )
        );

        $result = $generateAwb->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($result->getNoticeMessage()),
                $result->getNoticeType(),
            );
        }

        Redirector::to(
            'post.php',
            [
                'id' => $orderId,
                'post' => $orderId,
                'action' => 'edit',
            ]
        );
    }
}
