<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint;

use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete\DeletePickupPoint;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete\DeletePickupPointRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;

if (!defined('ABSPATH')) {
    exit;
}

final class DeletePickupPointController extends AbstractController
{
    private const ACTION = 'delete_pickup_point';

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
    public function processAction(array $inputParams): void
    {
        if (empty($inputParams)) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate("Unable to process the request."),
                ResponseNoticeType::ERROR,
            );

            Redirector::to('edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_pickup_points'
                ]
            );
        }

        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (SamedaySDKException $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate("Could not instantiate Sameday client service."),
                ResponseNoticeType::ERROR,
            );

            Redirector::to(
                'edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_pickup_points'
                ]
            );
        }

        if (null === $samedayId = $inputParams['sameday_id'] ?? null) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate("Invalid data format."),
                ResponseNoticeType::ERROR,
            );

            Redirector::to(
                'edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_pickup_points'
                ]
            );
        }

        $request = new DeletePickupPointRequest(
            $samedayApiClient,
            (int) $samedayId
        );

        $deletePickupPoint = new DeletePickupPoint($request);
        $result = $deletePickupPoint->execute();

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
