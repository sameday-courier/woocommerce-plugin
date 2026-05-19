<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint;

use Sameday\Exceptions\SamedaySDKException;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPoint;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPointRequest;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshPickupPointController extends AbstractController
{
    /**
     * @var string
     */
    private const ACTION = 'refresh_pickup_points';

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
     * @throws SamedaySDKException
     */
    protected function processPostAction(array $inputParams): void
    {
        $request = new RefreshPickupPointRequest(!empty(OptionsHandler::getSamedayOptions()));
        $refreshPickupPoint = new RefreshPickupPoint($request);

        $result = $refreshPickupPoint->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                'refresh_pickup_points_notice',
                $result->getNoticeMessage(),
                $result->getNoticeType(),
                true
            );
        }

        Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_pickup_points']);
    }
}
