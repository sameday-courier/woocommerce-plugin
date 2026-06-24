<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Refresh;

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Requests\SamedayGetServicesRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshService
{
    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @param RefreshServiceRequest $refreshServiceRequest
     */
    public function __construct(RefreshServiceRequest $refreshServiceRequest)
    {
        $this->sameday = $refreshServiceRequest->sameday;
        $this->samedayServiceRepository = $refreshServiceRequest->samedayServiceRepository;
    }

    /**
     * @return RefreshServiceResponse
     */
    public function execute(): RefreshServiceResponse
    {
        $remoteServices = [];
        $page = 1;

        do {
            $request = new SamedayGetServicesRequest();
            $request->setPage($page++);

            try {
                $services = $this->sameday->getServices($request);
            } catch (Exception $e) {
                return new RefreshServiceResponse(
                    $e->getMessage(),
                    ResponseNoticeType::ERROR,
                );
            }

            foreach ($services->getServices() as $serviceObject) {
                $service = $this->samedayServiceRepository->getServiceSameday($serviceObject->getId());
                if (null === $service) {
                    $this->samedayServiceRepository->addService($serviceObject);
                } else {
                    $this->samedayServiceRepository->updateServiceCode($serviceObject, $service->getId());
                }

                $remoteServices[] = $serviceObject->getId();
            }
        } while ($page <= $services->getPages());

        $localServices = array_map(
            static function (SamedayService $service) {
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
            SamedayConstants::LOCKER_NEXT_DAY_CODE
        );

        $pudoService = $this->samedayServiceRepository->getServiceSamedayByCode(
            SamedayConstants::PUDO_CODE
        );

        if (null !== $lnService && null !== $pudoService) {
            $this->samedayServiceRepository->updateService(
                [
                    'id' => $pudoService->getId(),
                    'status' => $lnService->getStatus(),
                ]
            );
        }

        return new RefreshServiceResponse(
            "Service successfully refreshed.",
            ResponseNoticeType::SUCCESS,
        );
    }
}
