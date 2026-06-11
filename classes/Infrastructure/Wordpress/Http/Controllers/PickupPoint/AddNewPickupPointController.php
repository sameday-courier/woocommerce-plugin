<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint;

use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew\AddNewPickupPoint;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew\AddNewPickupPointItem;
use SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew\AddNewPickupPointRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;

if (!defined('ABSPATH')) {
    exit;
}

class AddNewPickupPointController extends AbstractController
{
    private const ACTION = "send_pickup_point";

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
    public function processPostAction(array $inputParams): void
    {
        if (null === $formData = $inputParams['data'] ?? null) {
            Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_pickup_points']);
        }

        $requiredFields = [
            'pickupPointCountry',
            'pickupPointCounty',
            'pickupPointCity',
            'pickupPointAddress',
            'pickupPointPostalCode',
            'pickupPointAlias',
            'pickupPointContactPersonName',
            'pickupPointContactPersonPhone',
        ];

        foreach ($requiredFields as $field) {
            if (empty($formData[$field])) {
                // WIP treat form error ::
                Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_pickup_points']);
            }
        }

        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (SamedaySDKException $exception) {
            NoticerHandler::addFlashNotice(
                ResponseNoticeType::ERROR,
                TranslatorHandler::translate("Could not instantiate Sameday client service."),
                true
            );

            Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_pickup_points']);
        }

        $request = new AddNewPickupPointRequest(
            new AddNewPickupPointItem(
                $formData['pickupPointCountry'],
                $formData['pickupPointCounty'],
                $formData['pickupPointCity'],
                $formData['pickupPointAddress'],
                $formData['pickupPointPostalCode'],
                $formData['pickupPointAlias'],
                $formData['pickupPointContactPersonName'],
                $formData['pickupPointContactPersonPhone'],
                (bool) $formData['isDefault'],
            ),
            $samedayApiClient
        );

        $addNewPickupPoint = new AddNewPickupPoint($request);

        $result = $addNewPickupPoint->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
            );
        }

        Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_lockers']);
    }
}
