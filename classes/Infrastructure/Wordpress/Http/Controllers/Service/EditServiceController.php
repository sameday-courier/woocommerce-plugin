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
            $carrierSettingsProvider = new CarrierSettingsServiceProvider();
            $hostCountry = $carrierSettingsProvider->get()->getHostCountry();
            $serviceName = CarrierConstants::OOH_SERVICES_LABELS[$hostCountry];
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
}
