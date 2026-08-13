<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\ShippingMethodProviderInterface;
use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Domain\Shipping\ShippingMethodCodeParser;

final class WooShippingMethodProvider implements ShippingMethodProviderInterface
{
    /**
     * @var SessionHandlerInterface $sessionHandler
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
     * @return string
     */
    public function getChosenServiceCode(): string
    {
        $chosenShippingMethods = $this->sessionHandler->get(SamedaySessionKeys::CHOSEN_SHIPPING_METHODS);
        $chosenShippingMethod = is_array($chosenShippingMethods) ? ($chosenShippingMethods[0] ?? null) : null;

        if (null === $chosenShippingMethod) {
            return '';
        }

        return ShippingMethodCodeParser::parse($chosenShippingMethod);
    }
}
