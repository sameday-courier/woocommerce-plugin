<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\County;

use SamedayCourier\Shipping\Application\UseCases\County\Get\GetCountiesRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\GetCountiesFactory;

final class GetCountiesController extends AbstractController
{
    private const ACTION = 'get_counties';

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
        $getCounties = GetCountiesFactory::create();

        $result = $getCounties->execute(new GetCountiesRequest());

        if ($result->hasError()) {
            $this->sendJsonErrorResponse(
                TranslatorHandler::translate($result->getNoticeMessage()),
                500
            );
        }

        wp_send_json($result->getCounties());
    }
}
