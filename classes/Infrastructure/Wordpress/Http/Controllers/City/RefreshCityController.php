<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Application\Sql\SchemaHandler;
use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCity;
use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCityRequest;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CacheHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

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
        'section' => 'samedaycourier',
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
    protected function processPostAction(array $inputParams): void
    {
        $dbHandler = new DbHandler();
        $refreshCity = new RefreshCity(
            new RefreshCityRequest(
                new SchemaHandler(),
                $dbHandler,
                new SamedayCityRepository($dbHandler),
                new CacheHandler(),
            )
        );

        $result = $refreshCity->execute();
        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($result->getNoticeMessage()),
                $result->getNoticeType(),
            );
        }

        Redirector::to(
            'admin.php',
            self::SETTINGS_REDIRECT_ARGS
        );
    }
}
