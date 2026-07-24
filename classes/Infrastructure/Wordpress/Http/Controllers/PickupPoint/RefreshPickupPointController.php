<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint;


use Sameday\Sameday;
use Sameday\Exceptions\SamedaySDKException;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPoint;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPointRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
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
     */
    protected function processPostAction(array $inputParams): void
    {
        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (SamedaySDKException $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
                ResponseNoticeType::ERROR,
            );

            Redirector::to('edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_pickup_points'
                ]
            );
        }

        $request = new RefreshPickupPointRequest(
            $samedayApiClient,
            new SamedayPickupPointRepository()
        );
        $refreshPickupPoint = new RefreshPickupPoint($request);

        $result = $refreshPickupPoint->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
            );
        }

        Redirector::to(
            'edit.php',
            [
                'post_type' => 'page',
                'page' => 'sameday_pickup_points'
            ]
        );
    }
}
