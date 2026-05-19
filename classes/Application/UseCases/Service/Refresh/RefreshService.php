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

class RefreshService
{
    /**
     * @var RefreshServiceRequest $refreshServiceRequest
     */
    private RefreshServiceRequest $refreshServiceRequest;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @param RefreshServiceRequest $refreshServiceRequest
     */
    public function __construct(RefreshServiceRequest $refreshServiceRequest)
    {
        $this->refreshServiceRequest = $refreshServiceRequest;
        $this->samedayServiceRepository = new SamedayServiceRepository();
    }

    /**
     * @return RefreshServiceResponse
     *
     * @throws SamedaySDKException
     */
    public function execute(): RefreshServiceResponse
    {
        if (!$this->refreshServiceRequest->hasSamedayOptions()) {
            return new RefreshServiceResponse(
                ResponseNoticeType::ERROR,
                'Sameday options are not configured.',
            );
        }

        $sameday = new Sameday(SdkInitiator::init());
        $remoteServices = [];
        $page = 1;

        do {
            $request = new SamedayGetServicesRequest();
            $request->setPage($page++);

            try {
                $services = $sameday->getServices($request);
            } catch (Exception $e) {
                return new RefreshServiceResponse(
                    ResponseNoticeType::ERROR,
                    $e->getMessage(),
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

        return new RefreshServiceResponse(ResponseNoticeType::SUCCESS);
    }
}
