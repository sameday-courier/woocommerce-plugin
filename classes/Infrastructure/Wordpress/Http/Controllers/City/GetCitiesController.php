<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\City\Get\GetCities;
use SamedayCourier\Shipping\Application\UseCases\City\Get\GetCitiesItem;
use SamedayCourier\Shipping\Application\UseCases\City\Get\GetCitiesRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProviderService;

final class GetCitiesController extends AbstractController
{
    private const ACTION = 'get_cities';

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
        if (!isset($inputParams['countyId'])) {
            $this->sendJsonErrorResponse(
                TranslatorHandler::translate('County id is required.')
            );
        }

        $getCities = new GetCities(
            new GetCitiesRequest(
                GetCitiesItem::fromArray($inputParams),
                new CourierServiceProviderService()
            )
        );

        $result = $getCities->execute();

        if (ResponseNoticeType::ERROR === $result->getNoticeType()) {
            $this->sendJsonErrorResponse($result->getNoticeMessage(), 500);
        }

        wp_send_json($result->getCities());
    }
}
