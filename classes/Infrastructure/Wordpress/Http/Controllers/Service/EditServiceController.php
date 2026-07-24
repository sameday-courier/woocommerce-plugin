<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Service;

use SamedayCourier\Shipping\Application\UseCases\Service\Edit\EditService;
use SamedayCourier\Shipping\Application\UseCases\Service\Edit\EditServiceRequest;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Domain\SamedaySettings;

if (!defined('ABSPATH')) {
    exit;
}

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
    protected function processPostAction(array $inputParams): void
    {
        $serviceName = $inputParams['samedaycourier-service-name'] ?? null;
        if (null === $serviceName) {
            $serviceName = SamedayConstants::OOH_SERVICES_LABELS[SamedaySettings::getHostCountry()];
        }

        $editService = new EditService(
            new EditServiceRequest(
                (int) $inputParams['samedaycourier-service-id'],
                $serviceName,
                $inputParams['samedaycourier-price'],
                $inputParams['samedaycourier-free-delivery-price'] ?: null,
                $inputParams['samedaycourier-status'] ?? null,
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
            Redirector::to(
                'edit.php',
                [
                    'post_type' => 'page',
                    'page' => 'sameday_services',
                    'action' => 'edit',
                    'id' => $result->getServiceId(),
                ]
            );
        }

        Redirector::to(
            'edit.php',
            [
                'post_type' => 'page',
                'page' => 'sameday_services'
            ]
        );
    }
}
