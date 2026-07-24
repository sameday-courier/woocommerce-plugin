<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\County;

use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\County\Get\GetCounties;
use SamedayCourier\Shipping\Application\UseCases\County\Get\GetCountiesRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;

if (!defined('ABSPATH')) {
    exit;
}

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
    protected function processPostAction(array $inputParams): void
    {
        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (SamedaySDKException $exception) {
            wp_send_json_error(
                TranslatorHandler::translate('Could not instantiate Sameday client service.'),
                500
            );

            die();
        }

        $getCounties = new GetCounties(new GetCountiesRequest($samedayApiClient));
        $result = $getCounties->execute();

        if (ResponseNoticeType::ERROR === $result->getNoticeType()) {
            wp_send_json_error($result->getNoticeMessage(), 500);
        }

        wp_send_json($result->getCounties());
    }
}
