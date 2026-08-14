<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Service;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\UseCases\Service\Refresh\RefreshService;
use SamedayCourier\Shipping\Application\UseCases\Service\Refresh\RefreshServiceRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProviderService;

final class RefreshServiceController extends AbstractController
{
    /**
     * @var string
     */
    private const ACTION = 'refresh_services';

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
        $request = new RefreshServiceRequest(
            new CourierServiceProviderService(),
            new SamedayServiceRepository()
        );
        $refreshService = new RefreshService($request);

        $result = $refreshService->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
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
