<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetServicesRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\ServiceCatalogStoreServiceProviderInterface;

final class RefreshService
{
    private CourierServiceProviderInterface $courierServiceProvider;

    private ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore;

    public function __construct(RefreshServiceRequest $refreshServiceRequest)
    {
        $this->courierServiceProvider = $refreshServiceRequest->getCourierServiceProvider();
        $this->serviceCatalogStore = $refreshServiceRequest->getServiceCatalogStore();
    }

    public function execute(): RefreshServiceResponse
    {
        $remoteServices = [];
        $page = 1;

        do {
            try {
                $services = $this->courierServiceProvider->getServices(new GetServicesRequestDto($page++));
            } catch (CourierServiceException $exception) {
                return new RefreshServiceResponse(
                    $exception->getMessage(),
                    ResponseNoticeType::ERROR
                );
            }

            foreach ($services->getServices() as $serviceDto) {
                $service = $this->serviceCatalogStore->getBySamedayId($serviceDto->getId());
                if (null === $service) {
                    $this->serviceCatalogStore->add($serviceDto);
                } elseif (!$this->serviceCatalogStore->updateFromRemote($serviceDto, $service->getId())) {
                    return new RefreshServiceResponse(
                        'Unable to update service',
                        ResponseNoticeType::ERROR
                    );
                }

                $remoteServices[] = $serviceDto->getId();
            }
        } while ($page <= $services->getPages());

        $localServices = array_map(
            static function (CarrierService $service): array {
                return [
                    'id' => $service->getId(),
                    'sameday_id' => $service->getSamedayId(),
                ];
            },
            $this->serviceCatalogStore->getAll()
        );

        foreach ($localServices as $localService) {
            if (!in_array($localService['sameday_id'], $remoteServices, true)) {
                $this->serviceCatalogStore->deleteById((int) $localService['id']);
            }
        }

        $lnService = $this->serviceCatalogStore->getByCode(CarrierConstants::LOCKER_NEXT_DAY_CODE);
        $pudoService = $this->serviceCatalogStore->getByCode(CarrierConstants::PUDO_CODE);

        if (null !== $lnService && null !== $pudoService
            && !$this->serviceCatalogStore->updateFields(
                $pudoService->getId(),
                [
                    'status' => $lnService->getStatus(),
                ]
            )
        ) {
            return new RefreshServiceResponse(
                'Unable to sync PUDO status',
                ResponseNoticeType::ERROR
            );
        }

        return new RefreshServiceResponse(
            'Service successfully refreshed.',
            ResponseNoticeType::SUCCESS
        );
    }
}
