<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetServicesRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class RefreshService
{
    private CourierServiceProviderInterface $courier;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @param RefreshServiceRequest $refreshServiceRequest
     */
    public function __construct(RefreshServiceRequest $refreshServiceRequest)
    {
        $this->courier = $refreshServiceRequest->getCourier();
        $this->samedayServiceRepository = $refreshServiceRequest->getSamedayServiceRepository();
    }

    /**
     * @return RefreshServiceResponse
     */
    public function execute(): RefreshServiceResponse
    {
        $remoteServices = [];
        $page = 1;

        do {
            try {
                $services = $this->courier->getServices(new GetServicesRequestDto($page++));
            } catch (CourierServiceException $e) {
                return new RefreshServiceResponse(
                    $e->getMessage(),
                    ResponseNoticeType::ERROR,
                );
            }

            foreach ($services->getServices() as $serviceObject) {
                $service = $this->samedayServiceRepository->getServiceSameday($serviceObject->getId());
                if (null === $service) {
                    $this->samedayServiceRepository->addService($serviceObject);
                } elseif (!$this->samedayServiceRepository->updateServiceCode($serviceObject, $service->getId())) {
                    return new RefreshServiceResponse(
                        'Unable to update service',
                        ResponseNoticeType::ERROR,
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
            return new RefreshServiceResponse(
                'Unable to sync PUDO status',
                ResponseNoticeType::ERROR,
            );
        }

        return new RefreshServiceResponse(
            "Service successfully refreshed.",
            ResponseNoticeType::SUCCESS,
        );
    }
}
