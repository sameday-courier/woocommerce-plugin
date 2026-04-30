<?php

declare (strict_types = 1);

namespace SamedayCourier\Shipping\Application\DataSync;

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Requests\SamedayGetServicesRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;

if (!defined('ABSPATH')) {
    exit;
}

class RefreshServices
{
    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    public function __construct()
    {
        $this->samedayServiceRepository = new SamedayServiceRepository();
    }

    /**
     * @return void
     * @throws SamedaySDKException
     */
    public function refresh(): void
    {
        if (empty(OptionsHandler::getSamedayOptions())) {
            Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_services']);
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
                Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_services']);
            }

            foreach ($services->getServices() as $serviceObject) {
                $service = $this->samedayServiceRepository->getServiceSameday($serviceObject->getId());
                if (null === $service) {
                    // Service not found, add it.
                    $this->samedayServiceRepository->addService($serviceObject);
                } else {
                    $this->samedayServiceRepository->updateServiceCode($serviceObject, $service->getId());
                }

                // Save as current sameday service.
                $remoteServices[] = $serviceObject->getId();
            }
        } while ($page <= $services->getPages());

        // Build array of local services.
        $localServices = array_map(
            static function (SamedayService $service) {
                return array(
                    'id' => $service->getId(),
                    'sameday_id' => $service->getSamedayId()
                );
            },

            $this->samedayServiceRepository->getServices()
        );

        // Delete local services that aren't present in remote services anymore.
        foreach ($localServices as $localService) {
            if (!in_array($localService['sameday_id'], $remoteServices, true)) {
                $this->samedayServiceRepository->deleteService((int) $localService['id']);
            }
        }

        // Update PUDO Service
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

        Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_services']);
    }
}
