<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\AddNewParcelServiceProvider;

final class AddNewParcelAwbController extends AbstractController
{
    private const ACTION = "add-new-parcel";

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
     */
    protected function processAction(array $inputParams): void
    {
        $addNewParcelAwbItem = AddNewParcelAwbItem::fromArray($inputParams);
        $orderId = $addNewParcelAwbItem->getOrderId();

        try {
            $addNewParcelAwb = new AddNewParcelAwb(
                new AddNewParcelAwbRequest(
                    $addNewParcelAwbItem,
                    new AddNewParcelServiceProvider()
                )
            );
            $result = $addNewParcelAwb->execute();
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
            );

            $this->redirectTo(
                'post.php',
                [
                    'post' => $orderId,
                    'action' => 'edit',
                    'add-new-parcel' => ResponseNoticeType::ERROR,
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
                'post' => $result->getOrderId(),
                'action' => 'edit',
                'add-new-parcel' => $result->getNoticeType(),
            ]
        );
    }
}
