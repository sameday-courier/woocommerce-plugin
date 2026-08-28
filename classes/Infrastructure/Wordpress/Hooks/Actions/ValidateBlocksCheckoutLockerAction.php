<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use WP_REST_Request;

/**
 * Blocks/Store API equivalent of ValidateCheckoutLockerAction (woocommerce_checkout_process).
 *
 * Runs only on checkout POST (place order), not on draft PATCH updates.
 */
final class ValidateBlocksCheckoutLockerAction extends AbstractAction
{
    private const ACTION = 'woocommerce_store_api_checkout_update_order_from_request';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @return string[]|null
     */
    public function getParams(): ?array
    {
        return ['order', 'request'];
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     *
     * @throws RouteException
     */
    public function handle(...$args): void
    {
        if (!class_exists(RouteException::class)) {
            return;
        }

        $request = $args[1] ?? null;
        if (!$request instanceof WP_REST_Request || 'POST' !== $request->get_method()) {
            return;
        }

        $shippingMethodProvider = new WooShippingMethodProvider();
        $serviceCode = $shippingMethodProvider->getChosenServiceCode();
        if ('' === $serviceCode) {
            return;
        }

        $carrierServiceRules = new CarrierServiceRules(new SamedayServiceRepository());
        if (!$carrierServiceRules->isOohDeliveryOptionByCode($serviceCode)) {
            return;
        }

        $sessionHandler = new WooSessionHandler();
        if (null !== $sessionHandler->get(CarrierSessionKeys::LOCKER)) {
            return;
        }

        throw new RouteException(
            'sameday_locker_required',
            TranslatorHandler::translate('Please choose your EasyBox Locker !'),
            400
        );
    }
}
