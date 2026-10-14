<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Service;

use SamedayCourier\Shipping\Application\UseCases\Service\Edit\EditServiceRequest;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\EditServiceFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\EditServiceMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;
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
     * @param array $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $serviceName = $inputParams[EditServiceMapper::NAME_KEY] ?? null;
        if (null === $serviceName) {
            $serviceName = $this->lockedServiceName($inputParams);
        }

        $inputParams[EditServiceMapper::NAME_KEY] = $serviceName;

        $params = new EditServiceMapper($inputParams);
        $editService = EditServiceFactory::create();

        $result = $editService->execute(
            new EditServiceRequest(
                $params->id(),
                $params->name(),
                $params->price(),
                $params->priceFree(),
                $params->status()
            )
        );

        NoticerHandler::addFlashNotice(
            TranslatorHandler::translate($result->getNoticeMessage()),
            $result->hasError() ? ResponseNoticeType::ERROR : ResponseNoticeType::SUCCESS,
        );

        if ($result->hasError()) {
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

    /**
     * The name field is disabled for locker services, so it is not posted.
     * LN and XL each keep their own label.
     *
     * @param array $inputParams
     *
     * @return string
     */
    private function lockedServiceName(array $inputParams): string
    {
        $hostCountry = (new CarrierSettingsServiceProvider())->get()->getHostCountry();
        $serviceId = (int) ($inputParams[EditServiceMapper::ID_KEY] ?? 0);
        $service = (new ServiceCatalogStoreServiceProvider())->getById($serviceId);

        if (
            null !== $service
            && $service->getSamedayCode() === CarrierConstants::LOCKER_CROSSBORDER_CODE
        ) {
            return CarrierConstants::CROSSBORDER_OOH_SERVICES_LABELS[$hostCountry];
        }

        return CarrierConstants::OOH_SERVICES_LABELS[$hostCountry];
    }
}
