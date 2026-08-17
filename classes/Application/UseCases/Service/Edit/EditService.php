<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\EditServiceRequestDto;
use SamedayCourier\Shipping\Domain\Ports\EditServiceServiceProviderInterface;

final class EditService
{
    private EditServiceItem $editServiceItem;

    private EditServiceServiceProviderInterface $editServiceServiceProvider;

    /**
     * @param EditServiceRequest $editServiceRequest
     */
    public function __construct(EditServiceRequest $editServiceRequest)
    {
        $this->editServiceItem = $editServiceRequest->getEditServiceItem();
        $this->editServiceServiceProvider = $editServiceRequest->getEditServiceServiceProvider();
    }

    /**
     * @return EditServiceResponse
     */
    public function execute(): EditServiceResponse
    {
        $editServiceResponse = $this->editServiceServiceProvider->edit(
            new EditServiceRequestDto(
                $this->editServiceItem->getId(),
                $this->editServiceItem->getName(),
                $this->editServiceItem->getPrice(),
                $this->editServiceItem->getPriceFree(),
                $this->editServiceItem->getStatus()
            )
        );

        return new EditServiceResponse(
            $editServiceResponse->getServiceId(),
            $editServiceResponse->getMessage(),
            $editServiceResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
