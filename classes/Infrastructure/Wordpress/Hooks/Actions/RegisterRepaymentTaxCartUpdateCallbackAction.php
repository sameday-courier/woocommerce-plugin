<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Blocks\RepaymentTaxBlocksIntegration;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\ShippingRatesRefresher;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\ChosenPaymentMethodValidator;

final class RegisterRepaymentTaxCartUpdateCallbackAction extends AbstractAction
{
    private const ACTION = 'woocommerce_blocks_loaded';

    /**
     * @var SessionHandlerInterface $sessionHandler
     */
    private SessionHandlerInterface $sessionHandler;

    /**
     * @var ShippingRatesRefresher $shippingRatesRefresher
     */
    private ShippingRatesRefresher $shippingRatesRefresher;

    /**
     * @param SessionHandlerInterface|null $sessionHandler
     * @param ShippingRatesRefresher|null $shippingRatesRefresher
     */
    public function __construct(
        ?SessionHandlerInterface $sessionHandler = null,
        ?ShippingRatesRefresher $shippingRatesRefresher = null
    ) {
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
        $this->shippingRatesRefresher = $shippingRatesRefresher ?? new ShippingRatesRefresher();
    }

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
        if (!function_exists('woocommerce_store_api_register_update_callback')) {
            return;
        }

        woocommerce_store_api_register_update_callback(
            [
                'namespace' => RepaymentTaxBlocksIntegration::CART_UPDATE_NAMESPACE,
                'callback' => function ($data): void {
                    $this->store(is_array($data) ? $data : []);
                },
            ]
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    private function store(array $data): void
    {
        $paymentMethod = ChosenPaymentMethodValidator::normalize(
            $data[RepaymentTaxBlocksIntegration::CART_UPDATE_FIELD] ?? null
        );

        if (null === $paymentMethod) {
            return;
        }

        $this->sessionHandler->set(CarrierSessionKeys::CHOSEN_PAYMENT_METHOD, $paymentMethod);
        $this->shippingRatesRefresher->refresh();
    }
}
