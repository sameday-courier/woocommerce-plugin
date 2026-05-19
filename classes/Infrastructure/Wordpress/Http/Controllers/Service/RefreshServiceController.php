<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Service;

use Sameday\Exceptions\SamedaySDKException;
use SamedayCourier\Shipping\Application\UseCases\Service\Refresh\RefreshService;
use SamedayCourier\Shipping\Application\UseCases\Service\Refresh\RefreshServiceRequest;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;

if (!defined('ABSPATH')) {
    exit;
}

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
     * @throws SamedaySDKException
     */
    protected function processPostAction(array $inputParams): void
    {
        $request = new RefreshServiceRequest(!empty(OptionsHandler::getSamedayOptions()));
        $refreshService = new RefreshService($request);

        $result = $refreshService->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                'refresh_services_notice',
                $result->getNoticeMessage(),
                $result->getNoticeType(),
                true
            );
        }

        Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_services']);
    }
}
