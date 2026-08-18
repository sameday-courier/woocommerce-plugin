<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\Ports\ServiceCatalogStoreServiceProviderInterface;

final class EditService
{
    private EditServiceItem $editServiceItem;

    private ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore;

    public function __construct(EditServiceRequest $editServiceRequest)
    {
        $this->editServiceItem = $editServiceRequest->getEditServiceItem();
        $this->serviceCatalogStore = $editServiceRequest->getServiceCatalogStore();
    }

    public function execute(): EditServiceResponse
    {
        $serviceId = $this->editServiceItem->getId();
        $name = trim($this->editServiceItem->getName());
        $price = trim($this->editServiceItem->getPrice());
        $priceFreeRaw = $this->editServiceItem->getPriceFree();
        $statusRaw = $this->editServiceItem->getStatus();

        $errors = [];
        if ('' === $name) {
            $errors[] = 'The name must not be empty';
        }
        if ('' === $price) {
            $errors[] = 'The price must not be empty';
        }

        if ([] !== $errors) {
            return new EditServiceResponse(
                $serviceId,
                implode(' ', $errors),
                ResponseNoticeType::ERROR
            );
        }

        $priceFree = null;
        if (null !== $priceFreeRaw && (float) $priceFreeRaw > 0) {
            $priceFree = (float) $priceFreeRaw;
        }

        $currentService = $this->serviceCatalogStore->getById($serviceId);
        if (null === $currentService) {
            return new EditServiceResponse(
                $serviceId,
                "Unable to find service $serviceId",
                ResponseNoticeType::ERROR
            );
        }

        $status = (int) $statusRaw;

        if (
            !$this->serviceCatalogStore->updateFields(
                $serviceId,
                [
                'name' => $name,
                'price' => (float) $price,
                'price_free' => $priceFree,
                'status' => $status,
                ]
            )
        ) {
            return new EditServiceResponse(
                $serviceId,
                'Unable to update service',
                ResponseNoticeType::ERROR
            );
        }

        if ($currentService->getSamedayCode() === CarrierConstants::LOCKER_NEXT_DAY_CODE) {
            $pudoService = $this->serviceCatalogStore->getByCode(CarrierConstants::PUDO_CODE);

            if (
                null !== $pudoService
                && !$this->serviceCatalogStore->updateFields(
                    $pudoService->getId(),
                    [
                        'status' => $status,
                    ]
                )
            ) {
                return new EditServiceResponse(
                    $serviceId,
                    'Service updated, but unable to sync PUDO status',
                    ResponseNoticeType::ERROR
                );
            }
        }

        return new EditServiceResponse(
            $serviceId,
            'Service has been edited',
            ResponseNoticeType::SUCCESS
        );
    }
}
