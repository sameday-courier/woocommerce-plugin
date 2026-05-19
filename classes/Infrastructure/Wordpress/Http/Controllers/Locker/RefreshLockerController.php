<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Locker;

use Sameday\Exceptions\SamedaySDKException;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLocker;
use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLockerRequest;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;

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
     * @throws SamedaySDKException
     */
    protected function processPostAction(array $inputParams): void
    {
        $request = new RefreshLockerRequest(!empty(OptionsHandler::getSamedayOptions()));
        $refreshLocker = new RefreshLocker($request);

        $result = $refreshLocker->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                'refresh_lockers_notice',
                $result->getNoticeMessage(),
                $result->getNoticeType(),
                true
            );
        }

        Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_lockers']);
    }
}
