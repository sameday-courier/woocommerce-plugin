<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class ValidateCheckoutLockerAction extends AbstractAction
{
    private const ACTION = 'woocommerce_checkout_process';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        $serviceCode = (new WooShippingMethodProvider())->getChosenServiceCode();
        if ('' === $serviceCode) {
            return;
        }

        $samedayServiceRules = new SamedayServiceRules(new SamedayServiceRepository());
        $isOohDelivery = $samedayServiceRules->isOohDeliveryOptionByCode($serviceCode);
        $isOohButUserNotSelectLocker = $isOohDelivery && (null === (new WooSessionHandler())->get(SamedaySessionKeys::LOCKER));

        if ($isOohButUserNotSelectLocker) {
            wc_add_notice(TranslatorHandler::translate('Please choose your EasyBox Locker !'), 'error');
        }
    }
}
