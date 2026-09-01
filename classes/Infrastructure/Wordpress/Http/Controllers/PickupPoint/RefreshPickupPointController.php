<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint;

use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPointRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\RefreshPickupPointFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;

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
     * @param array $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $refreshPickupPoint = RefreshPickupPointFactory::create();

        $result = $refreshPickupPoint->execute(new RefreshPickupPointRequest());

        NoticerHandler::addFlashNotice(
            TranslatorHandler::translate($result->getNoticeMessage()),
            $result->hasError() ? ResponseNoticeType::ERROR : ResponseNoticeType::SUCCESS,
        );

        $this->redirectTo(
            'edit.php',
            [
                'post_type' => 'page',
                'page' => 'sameday_pickup_points'
            ]
        );
    }
}
