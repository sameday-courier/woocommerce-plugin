<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City;

use SamedayCourier\Shipping\Application\Common\Factories\CityRequestFactory;
use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCity;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;

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
     * @param array<string, mixed> $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $refreshCity = new RefreshCity(
            (new CityRequestFactory())->create()
        );

        $result = $refreshCity->execute();
        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($result->getNoticeMessage()),
                $result->getNoticeType(),
            );
        }

        $this->redirectTo(
            'admin.php',
            self::SETTINGS_REDIRECT_ARGS
        );
    }
}
