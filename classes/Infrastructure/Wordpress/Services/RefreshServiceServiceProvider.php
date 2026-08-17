<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetServicesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RefreshServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RefreshServiceResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\RefreshServiceServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class RefreshServiceServiceProvider implements RefreshServiceServiceProviderInterface
{
    private CourierServiceProviderInterface $courier;

    private SamedayServiceRepository $samedayServiceRepository;

    public function __construct(
        ?CourierServiceProviderInterface $courier = null,
        ?SamedayServiceRepository $samedayServiceRepository = null
    ) {
        $this->courier = $courier ?? new CourierServiceProvider();
        $this->samedayServiceRepository = $samedayServiceRepository ?? new SamedayServiceRepository();
    }

    /**
     * @param RefreshServiceRequestDto $refreshServiceRequestDto
     *
     * @return RefreshServiceResponseDto
     */
    public function refresh(RefreshServiceRequestDto $refreshServiceRequestDto): RefreshServiceResponseDto
    {
        $remoteServices = [];
        $page = 1;

        do {
            try {
                $services = $this->courier->getServices(new GetServicesRequestDto($page++));
            } catch (CourierServiceException $e) {
                return new RefreshServiceResponseDto(
                    false,
                    $e->getMessage()
                );
            }

            foreach ($services->getServices() as $serviceObject) {
                $service = $this->samedayServiceRepository->getServiceSameday($serviceObject->getId());
                if (null === $service) {
                    $this->samedayServiceRepository->addService($serviceObject);
                } elseif (!$this->samedayServiceRepository->updateServiceCode($serviceObject, $service->getId())) {
                    return new RefreshServiceResponseDto(
                        false,
                        'Unable to update service'
                    );
                }

                $remoteServices[] = $serviceObject->getId();
            }
        } while ($page <= $services->getPages());

        $localServices = array_map(
            static function (CarrierService $service) {
                return [
                    'id' => $service->getId(),
                    'sameday_id' => $service->getSamedayId(),
                ];
            },
            $this->samedayServiceRepository->getServices()
        );

        foreach ($localServices as $localService) {
            if (!in_array($localService['sameday_id'], $remoteServices, true)) {
                $this->samedayServiceRepository->deleteService((int) $localService['id']);
            }
        }

        $lnService = $this->samedayServiceRepository->getServiceSamedayByCode(
            CarrierConstants::LOCKER_NEXT_DAY_CODE
        );

        $pudoService = $this->samedayServiceRepository->getServiceSamedayByCode(
            CarrierConstants::PUDO_CODE
        );

        if (null !== $lnService && null !== $pudoService
            && !$this->samedayServiceRepository->updateService(
                [
                    'id' => $pudoService->getId(),
                    'status' => $lnService->getStatus(),
                ]
            )
        ) {
            return new RefreshServiceResponseDto(
                false,
                'Unable to sync PUDO status'
            );
        }

        return new RefreshServiceResponseDto(
            true,
            "Service successfully refreshed."
        );
    }
}
