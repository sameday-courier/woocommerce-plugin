<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PostRemoveAwbServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class RemoveAwbController extends AbstractController
{
    private const ACTION = 'remove-awb';

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
        $removeAwbItem = RemoveAwbItem::fromArray($inputParams);

        try {
            $samedayAwbRepository = new SamedayAwbRepository();
            $removeAwb = new RemoveAwb(
                new RemoveAwbRequest(
                    $removeAwbItem,
                    new OrderAwbStoreServiceProvider($samedayAwbRepository),
                    new CourierServiceProvider(),
                    new PostRemoveAwbServiceProvider($samedayAwbRepository)
                )
            );
            $result = $removeAwb->execute();
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
            );

            $this->redirectToOrderEdit($removeAwbItem->getOrderId());

            return;
        }

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
            );
        }

        $this->redirectToOrderEdit($removeAwbItem->getOrderId());
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    private function redirectToOrderEdit(int $orderId): void
    {
        $this->redirectTo(
            'post.php',
            [
                'post' => $orderId,
                'action' => 'edit',
            ]
        );
    }
}
