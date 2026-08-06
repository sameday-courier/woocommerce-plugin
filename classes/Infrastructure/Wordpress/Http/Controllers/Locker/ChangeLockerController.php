<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Locker;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Locker\Change\ChangeLocker;
use SamedayCourier\Shipping\Application\UseCases\Locker\Change\ChangeLockerRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooLockerOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;

if (!defined('ABSPATH')) {
    exit;
}

final class ChangeLockerController extends AbstractController
{
    /**
     * @var string
     */
    private const ACTION = 'change_locker';

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
        $orderId = isset($inputParams['orderId']) ? (int) $inputParams['orderId'] : 0;
        $locker = $inputParams['locker'] ?? null;

        $result = (new ChangeLocker(
            new ChangeLockerRequest(
                $orderId,
                $locker,
                new WooLockerOrderDataHandler(),
            )
        ))->execute();

        if (ResponseNoticeType::ERROR === $result->getNoticeType()) {
            wp_send_json_error(
                TranslatorHandler::translate($result->getNoticeMessage() ?? 'Failed to change locker.'),
                400
            );
        }

        wp_send_json_success();
    }
}
