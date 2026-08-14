<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\SchemaHandler;
use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCity;
use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCityRequest;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\CacheHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;

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
        $dbHandler = new DbHandler();
        $refreshCity = new RefreshCity(
            new RefreshCityRequest(
                new SchemaHandler(),
                $dbHandler,
                new SamedayCityRepository($dbHandler),
                new CacheHandler(),
                new WooCountriesHandler(),
            )
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
