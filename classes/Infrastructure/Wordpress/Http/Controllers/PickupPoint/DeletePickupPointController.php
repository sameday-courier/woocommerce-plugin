<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint;

use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete\DeletePickupPointRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\DeletePickupPointFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\DeletePickupPointMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;

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

        if (null === ($inputParams[DeletePickupPointMapper::SAMEDAY_ID_KEY] ?? null)) {
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

        $params = new DeletePickupPointMapper($inputParams);
        $deletePickupPoint = DeletePickupPointFactory::create();

        $result = $deletePickupPoint->execute(
            new DeletePickupPointRequest(
                $params->samedayId()
            )
        );

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
