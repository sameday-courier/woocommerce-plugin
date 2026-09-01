<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint;

use SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew\AddNewPickupPointRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\AddNewPickupPointFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\AddNewPickupPointMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;

class AddNewPickupPointController extends AbstractController
{
    private const ACTION = 'send_pickup_point';

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

        $requiredFields = [
            AddNewPickupPointMapper::COUNTRY_KEY,
            AddNewPickupPointMapper::COUNTY_KEY,
            AddNewPickupPointMapper::CITY_KEY,
            AddNewPickupPointMapper::ADDRESS_KEY,
            AddNewPickupPointMapper::POSTAL_CODE_KEY,
            AddNewPickupPointMapper::ALIAS_KEY,
            AddNewPickupPointMapper::CONTACT_PERSON_NAME_KEY,
            AddNewPickupPointMapper::CONTACT_PERSON_PHONE_KEY,
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
            );

            $this->redirectTo(
                'edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_pickup_points',
                ]
            );
        }

        $params = new AddNewPickupPointMapper($inputParams);
        $addNewPickupPoint = AddNewPickupPointFactory::create();

        $result = $addNewPickupPoint->execute(
            new AddNewPickupPointRequest(
                $params->pickupPointCountryId(),
                $params->pickupPointCountyId(),
                $params->pickupPointCityId(),
                $params->pickupPointAddress(),
                $params->pickupPointPostalCode(),
                $params->pickupPointAlias(),
                $params->pickupPointContactPersonName(),
                $params->pickupPointContactPersonPhone(),
                $params->isDefault()
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
                'page' => 'sameday_pickup_points',
            ]
        );
    }
}
