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
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;

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
    public function processAction(array $inputParams): void
    {
        if (empty($inputParams)) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate("Unable to process the request."),
                ResponseNoticeType::ERROR,
            );

            $this->redirectTo('edit.php',
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

            $this->redirectTo('edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_pickup_points'
                ]
            );
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

        $requiredFieldsErrors = [];
        foreach ($requiredFields as $field) {
            if (empty($inputParams[$field])) {
                // WIP treat form error ::
                $requiredFieldsErrors[] = sprintf("%s is required.", $field);
            }
        }

        if (!empty($requiredFieldsErrors)) {
            $errorMessage = implode(" <br/> ", $requiredFieldsErrors);
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($errorMessage),
                ResponseNoticeType::ERROR,
            );

            $this->redirectTo('edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_pickup_points'
                ]
            );
        }

        $request = new AddNewPickupPointRequest(
            AddNewPickupPointItem::fromArray($inputParams),
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

        $this->redirectTo('edit.php',
            [
                'post_type' => 'page',
                'page' => 'sameday_pickup_points'
            ]
        );
    }
}
