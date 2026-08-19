<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete\DeletePickupPoint;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete\DeletePickupPointItem;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete\DeletePickupPointRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;

final class DeletePickupPointController extends AbstractController
{
    private const ACTION = 'delete_pickup_point';

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
    public function processAction(array $inputParams): void
    {
        if (empty($inputParams)) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate('Unable to process the request.'),
                ResponseNoticeType::ERROR,
            );

            $this->redirectTo(
                'edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_pickup_points',
                ]
            );
        }

        if (null === ($inputParams['sameday_id'] ?? null)) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate('Invalid data format.'),
                ResponseNoticeType::ERROR,
            );

            $this->redirectTo(
                'edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_pickup_points',
                ]
            );
        }

        $deletePickupPointItem = DeletePickupPointItem::fromArray($inputParams);

        $request = new DeletePickupPointRequest(
            $deletePickupPointItem,
            new CourierServiceProvider()
        );

        $deletePickupPoint = new DeletePickupPoint($request);
        $result = $deletePickupPoint->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
            );
        }

        $this->redirectTo(
            'edit.php',
            [
                'post_type' => 'page',
                'page' => 'sameday_pickup_points'
            ]
        );
    }
}
