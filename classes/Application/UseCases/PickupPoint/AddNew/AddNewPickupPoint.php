<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use Exception;
use Sameday\Objects\PickupPoint\PickupPointContactPersonObject;
use Sameday\Requests\SamedayPostPickupPointRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;

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
        $this->addNewPickupPointItem = $addNewPickupPointRequest->getAddNewPickupPointItem();
        $this->sameday = $addNewPickupPointRequest->getSameday();
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
            $this->addNewPickupPointItem->getPickupPointPostalCode(),
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
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new AddNewPickupPointResponse(
            'Successfully added new pickup point.',
            ResponseNoticeType::SUCCESS,
        );
    }
}
