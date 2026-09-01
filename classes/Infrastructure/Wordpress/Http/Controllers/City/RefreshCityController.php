<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City;

use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCityRequest;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\RefreshCityFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;

final class RefreshCityController extends AbstractController
{
    /**
     * @var string
     */
    private const ACTION = 'import_cities';

    /**
     * @var array<string, string>
     */
    private const SETTINGS_REDIRECT_ARGS = [
        'page' => 'wc-settings',
        'tab' => 'shipping',
        'section' => CarrierConstants::PLUGIN_NAME,
    ];

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
        $refreshCity = RefreshCityFactory::create();

        $result = $refreshCity->execute(new RefreshCityRequest());

        NoticerHandler::addFlashNotice(
            TranslatorHandler::translate($result->getNoticeMessage()),
            $result->hasError() ? ResponseNoticeType::ERROR : ResponseNoticeType::SUCCESS,
        );

        $this->redirectTo(
            'admin.php',
            self::SETTINGS_REDIRECT_ARGS
        );
    }
}
