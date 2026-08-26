<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City;

use SamedayCourier\Shipping\Application\UseCases\City\Get\GetCitiesRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\GetCitiesFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\GetCitiesMapper;

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
     * @param array $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        if (!isset($inputParams[GetCitiesMapper::COUNTY_ID_KEY])) {
            $this->sendJsonErrorResponse(
                TranslatorHandler::translate('County id is required.')
            );
        }

        $params = new GetCitiesMapper($inputParams);
        $getCities = GetCitiesFactory::create();

        $result = $getCities->execute(
            new GetCitiesRequest(
                $params->countyId()
            )
        );

        if ($result->hasError()) {
            $this->sendJsonErrorResponse(
                TranslatorHandler::translate($result->getNoticeMessage()),
                500
            );
        }

        wp_send_json($result->getCities());
    }
}
