<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint;

use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPoint;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPointRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PickupPointStoreServiceProvider;

final class RefreshPickupPointController extends AbstractController
{
    /**
     * @var string
     */
    private const ACTION = 'refresh_pickup_points';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array<string, mixed> $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $request = new RefreshPickupPointRequest(
            new CourierServiceProvider(),
            new PickupPointStoreServiceProvider()
        );
        $refreshPickupPoint = new RefreshPickupPoint($request);

        $result = $refreshPickupPoint->execute();

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
