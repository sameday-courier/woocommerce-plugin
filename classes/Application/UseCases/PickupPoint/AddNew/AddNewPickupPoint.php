<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use Exception;
use Sameday\Objects\PickupPoint\PickupPointContactPersonObject;
use Sameday\Requests\SamedayPostPickupPointRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;

if (!defined('ABSPATH')) {
    exit;
}

final class AddNewPickupPoint
{
    /**
     * @var AddNewPickupPointItem $addNewPickupPointItem
     */
    private AddNewPickupPointItem $addNewPickupPointItem;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    public function __construct(
        AddNewPickupPointRequest $addNewPickupPointRequest
    )
    {
        $this->addNewPickupPointItem = $addNewPickupPointRequest->pickupPointItem;
        $this->sameday = $addNewPickupPointRequest->sameday;
    }

    /**
     * @return AddNewPickupPointResponse
     */
    public function execute(): AddNewPickupPointResponse
    {
        $request = new SamedayPostPickupPointRequest(
            $this->addNewPickupPointItem->getPickupPointCountryId(),
            $this->addNewPickupPointItem->getPickupPointCountyId(),
            $this->addNewPickupPointItem->getPickupPointCityId(),
            $this->addNewPickupPointItem->getPickupPointAddress(),
            $this->addNewPickupPointItem->getPickupPointAddress(),
            $this->addNewPickupPointItem->getPickupPointAlias(),
            [
                new PickupPointContactPersonObject(
                    $this->addNewPickupPointItem->getPickupPointContactPersonName(),
                    $this->addNewPickupPointItem->getPickupPointContactPersonPhone(),
                    true,
                )
            ],
            $this->addNewPickupPointItem->isDefault(),
        );
        try {
            $this->sameday->postPickupPoint($request);
        } catch (Exception $exception) {
            return new AddNewPickupPointResponse(
                $exception->getCode(),
                $exception->getMessage(),
            );
        }

        return new AddNewPickupPointResponse(
            ResponseNoticeType::SUCCESS,
            'Successfully added new pickup point.',
        );
    }
}
