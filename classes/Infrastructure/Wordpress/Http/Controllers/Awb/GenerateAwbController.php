<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use JsonException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Factories\AwbRequestFactory;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingHandler;

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
     * @throws JsonException
     */
    protected function processAction(array $inputParams): void
    {
        $orderId = (int) $inputParams['samedaycourier-order-id'];
        $orderData = (new WooShippingHandler())->getShippingOrderById($orderId);

        if (empty($orderData)) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate("There is no data to process."),
            );

            $this->redirectTo($orderId);

            return;
        }

        $data = array_merge($inputParams, $orderData);
        $generateAwbItem = GenerateAwbItem::fromArray($data);

        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
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

        $awbRequestFactory = new AwbRequestFactory();
        $generateAwb = new GenerateAwb(
            $awbRequestFactory->create(
                $generateAwbItem,
                $samedayApiClient
            )
        );

        $result = $generateAwb->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($result->getNoticeMessage()),
                $result->getNoticeType(),
            );
        }

        $this->redirectTo($orderId);
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    private function redirectTo(int $orderId): void
    {
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
