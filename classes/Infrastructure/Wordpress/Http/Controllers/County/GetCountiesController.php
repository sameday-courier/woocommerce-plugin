<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\County;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\County\Get\GetCounties;
use SamedayCourier\Shipping\Application\UseCases\County\Get\GetCountiesRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\GetCountiesServiceProvider;

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
     * @param array<string, mixed> $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $getCounties = new GetCounties(new GetCountiesRequest(new GetCountiesServiceProvider()));
        $result = $getCounties->execute();

        if (ResponseNoticeType::ERROR === $result->getNoticeType()) {
            $this->sendJsonErrorResponse($result->getNoticeMessage(), 500);
        }

        wp_send_json($result->getCounties());
    }
}
