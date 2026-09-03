<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\Ports\ServiceCatalogStoreServiceProviderInterface;

/**
 * @extends AbstractUseCase<EditServiceRequest, EditServiceResponse>
 *
 * @method EditServiceResponse execute(EditServiceRequest $request)
 */
final class EditService extends AbstractUseCase
{
    /**
     * @var ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore
     */
    private ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore;

    /**
     * @param ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore
     */
    public function __construct(
        ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore
    ) {
        $this->serviceCatalogStore = $serviceCatalogStore;
    }

    /**
     * @param EditServiceRequest $request
     * @return EditServiceResponse
     */
    protected function processAction(RequestInterface $request): EditServiceResponse
    {
        $serviceId = $request->getId();
        $name = trim($request->getName());
        $price = trim($request->getPrice());
        $priceFreeRaw = $request->getPriceFree();
        $statusRaw = $request->getStatus();

        $errors = [];
        if ('' === $name) {
            $errors[] = 'The name must not be empty';
        }
        if ('' === $price) {
            $errors[] = 'The price must not be empty';
        }

        if ([] !== $errors) {
            return new EditServiceResponse(
                implode(' ', $errors),
                true,
                $serviceId
            );
        }

        $priceFree = null;
        if (null !== $priceFreeRaw && (float) $priceFreeRaw > 0) {
            $priceFree = (float) $priceFreeRaw;
        }

        $currentService = $this->serviceCatalogStore->getById($serviceId);
        if (null === $currentService) {
            return new EditServiceResponse(
                "Unable to find service $serviceId",
                true,
                $serviceId
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
                'Unable to update service',
                true,
                $serviceId
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
                    'Service updated, but unable to sync PUDO status',
                    true,
                    $serviceId
                );
            }
        }

        return new EditServiceResponse(
            'Service has been edited',
            false,
            $serviceId
        );
    }
}
