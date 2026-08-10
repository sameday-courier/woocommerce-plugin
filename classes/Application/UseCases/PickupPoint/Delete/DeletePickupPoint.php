<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use Exception;
use Sameday\Requests\SamedayDeletePickupPointRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;

if (!defined('ABSPATH')) {
    exit;
}

final class DeletePickupPoint
{
    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var int $samedayId
     */
    private int $samedayId;

    /**
     * @param DeletePickupPointRequest $deletePickupPointRequest
     */
    public function __construct(DeletePickupPointRequest $deletePickupPointRequest)
    {
        $this->sameday = $deletePickupPointRequest->getSameday();
        $this->samedayId = $deletePickupPointRequest->getDeletePickupPointItem()->getSamedayId();
    }

    /**
     * @return DeletePickupPointResponse
     */
    public function execute(): DeletePickupPointResponse
    {
        return new DeletePickupPointResponse(
            'Pickup point successfully deleted.',
            ResponseNoticeType::SUCCESS,
        );

        try {
            // $this->sameday->deletePickupPoint(new SamedayDeletePickupPointRequest($this->samedayId));
        } catch (Exception $exception) {
            return new DeletePickupPointResponse(
                'Failed to delete pickup point: ' . $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new DeletePickupPointResponse(
            'Pickup point successfully deleted.',
            ResponseNoticeType::SUCCESS,
        );
    }
}
