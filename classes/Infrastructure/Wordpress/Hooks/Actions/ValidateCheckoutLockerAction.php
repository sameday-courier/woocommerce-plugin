<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\LockerDtoFactoryFactory;

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
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        $shippingMethodProvider = new WooShippingMethodProvider();
        $serviceCode = $shippingMethodProvider->getChosenServiceCode();
        if ('' === $serviceCode) {
            return;
        }

        $carrierServiceRules = new CarrierServiceRules(new SamedayServiceRepository());
        $isOohDelivery = $carrierServiceRules->isOohDeliveryOptionByCode($serviceCode);
        if (!$isOohDelivery) {
            return;
        }

        $sessionHandler = new WooSessionHandler();
        $locker = $sessionHandler->get(CarrierSessionKeys::LOCKER);
        $lockerDto = LockerDtoFactoryFactory::create();
        $isMalformedLocker = null !== $locker && '' !== $locker
            && null === $lockerDto->fromInput($locker);
        if ($isMalformedLocker) {
            $sessionHandler->set(CarrierSessionKeys::LOCKER, '');
        }

        if (null === $locker || '' === $locker || $isMalformedLocker) {
            wc_add_notice(TranslatorHandler::translate('Please choose your EasyBox Locker !'), 'error');
        }
    }
}
