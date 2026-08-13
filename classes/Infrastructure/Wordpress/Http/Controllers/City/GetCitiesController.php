<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City;

use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\City\Get\GetCities;
use SamedayCourier\Shipping\Application\UseCases\City\Get\GetCitiesItem;
use SamedayCourier\Shipping\Application\UseCases\City\Get\GetCitiesRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;

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

        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (SamedaySDKException $exception) {
            $this->sendJsonErrorResponse(
                TranslatorHandler::translate('Could not instantiate Sameday client service.'),
                500
            );
        }

        $getCities = new GetCities(
            new GetCitiesRequest(
                GetCitiesItem::fromArray($inputParams),
                $samedayApiClient
            )
        );

        $result = $getCities->execute();

        if (ResponseNoticeType::ERROR === $result->getNoticeType()) {
            $this->sendJsonErrorResponse($result->getNoticeMessage(), 500);
        }

        wp_send_json($result->getCities());
    }
}
