<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew\AddNewPickupPoint;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew\AddNewPickupPointItem;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew\AddNewPickupPointRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProviderService;

class AddNewPickupPointController extends AbstractController
{
    private const ACTION = 'send_pickup_point';

    public function getAction(): string
    {
        return self::ACTION;
    }

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

        $requiredFields = [
            'pickupPointCountry',
            'pickupPointCounty',
            'pickupPointCity',
            'pickupPointAddress',
            'pickupPointPostalCode',
            'pickupPointAlias',
            'pickupPointContactPersonName',
            'pickupPointContactPersonPhone',
        ];

        $requiredFieldsErrors = [];
        foreach ($requiredFields as $field) {
            if (empty($inputParams[$field])) {
                $requiredFieldsErrors[] = sprintf('%s is required.', $field);
            }
        }

        if (!empty($requiredFieldsErrors)) {
            $errorMessage = implode(' <br/> ', $requiredFieldsErrors);
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($errorMessage),
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

        $request = new AddNewPickupPointRequest(
            AddNewPickupPointItem::fromArray($inputParams),
            new CourierServiceProviderService()
        );

        $addNewPickupPoint = new AddNewPickupPoint($request);

        $result = $addNewPickupPoint->execute();

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
                'page' => 'sameday_pickup_points',
            ]
        );
    }
}
