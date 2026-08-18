<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Service;

use SamedayCourier\Shipping\Application\UseCases\Service\Edit\EditService;
use SamedayCourier\Shipping\Application\UseCases\Service\Edit\EditServiceItem;
use SamedayCourier\Shipping\Application\UseCases\Service\Edit\EditServiceRequest;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\ServiceCatalogStoreServiceProvider;

final class EditServiceController extends AbstractController
{
    /**
     * @var string
     */
    private const ACTION = 'edit-service';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array<string, mixed> $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $serviceName = $inputParams['samedaycourier-service-name'] ?? null;
        if (null === $serviceName) {
            $carrierSettingsProvider = new CarrierSettingsServiceProvider();
            $hostCountry = $carrierSettingsProvider->get()->getHostCountry();
            $serviceName = CarrierConstants::OOH_SERVICES_LABELS[$hostCountry];
        }

        $inputParams['samedaycourier-service-name'] = $serviceName;

        $editService = new EditService(
            new EditServiceRequest(
                EditServiceItem::fromArray($inputParams),
                new ServiceCatalogStoreServiceProvider()
            )
        );

        $result = $editService->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
            );
        }

        if ($result->getNoticeType() === ResponseNoticeType::ERROR) {
            $this->redirectTo(
                'edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_services',
                    'action' => 'edit',
                    'id' => $result->getServiceId(),
                ]
            );
        }

        $this->redirectTo(
            'edit.php',
            [
                'post_type' => 'page',
                'page' => 'sameday_services'
            ]
        );
    }
}
