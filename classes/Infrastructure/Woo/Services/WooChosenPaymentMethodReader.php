<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\Ports\ChosenPaymentMethodReaderInterface;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;

final class WooChosenPaymentMethodReader implements ChosenPaymentMethodReaderInterface
{
    /**
     * @var SessionHandlerInterface
     */
    private SessionHandlerInterface $sessionHandler;

    /**
     * @param SessionHandlerInterface|null $sessionHandler
     */
    public function __construct(?SessionHandlerInterface $sessionHandler = null)
    {
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
    }

    /**
     * WooCommerce stores the checkout payment gateway id under chosen_payment_method.
     *
     * @return string|null
     */
    public function getChosenPaymentMethod(): ?string
    {
        $paymentMethod = $this->sessionHandler->get(CarrierSessionKeys::CHOSEN_PAYMENT_METHOD);

        if (!is_string($paymentMethod) || '' === $paymentMethod) {
            return null;
        }

        return $paymentMethod;
    }
}
