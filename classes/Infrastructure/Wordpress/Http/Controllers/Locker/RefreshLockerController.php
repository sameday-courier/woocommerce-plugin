<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Locker;

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLocker;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLockerRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshLockerController extends AbstractController
{
    /**
     * @var string
     */
    private const ACTION = 'refresh_lockers';

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
        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                ResponseNoticeType::ERROR,
                TranslatorHandler::translate("Could not instantiate Sameday client service."),
            );

            Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_lockers']);
        }

        $request = new RefreshLockerRequest(
            new SamedayLockerRepository(),
            $samedayApiClient
        );
        $refreshLocker = new RefreshLocker($request);

        $result = $refreshLocker->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
            );
        }

        Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_lockers']);
    }
}
