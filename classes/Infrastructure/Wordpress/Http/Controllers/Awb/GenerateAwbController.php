<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\GenerateAwbServiceProvider;

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
     */
    protected function processAction(array $inputParams): void
    {
        $orderId = (int) $inputParams['samedaycourier-order-id'];
        $orderData = (new WooShippingHandler())->getShippingOrderById($orderId);

        if (empty($orderData)) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate("There is no data to process."),
            );

            $this->redirectTo(
                'post.php',
                [
                    'id' => $orderId,
                    'post' => $orderId,
                    'action' => 'edit',
                ]
            );
        }

        $data = array_merge($inputParams, $orderData);
        $generateAwbItem = GenerateAwbItem::fromArray($data);

        try {
            $generateAwb = new GenerateAwb(
                new GenerateAwbRequest(
                    $generateAwbItem,
                    new GenerateAwbServiceProvider()
                )
            );
            $result = $generateAwb->execute();
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
            );

            $this->redirectTo(
                'post.php',
                [
                    'post' => $orderId,
                    'action' => 'edit',
                    'add-awb' => ResponseNoticeType::ERROR,
                ]
            );
        }

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($result->getNoticeMessage()),
                $result->getNoticeType(),
            );
        }

        $this->redirectTo(
            'post.php',
            [
                'id' => $orderId,
                'post' => $orderId,
                'action' => 'edit',
            ]
        );
    }
}
