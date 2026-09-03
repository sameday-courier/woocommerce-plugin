<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface ChosenPaymentMethodReaderInterface
{
    /**
     * @return string|null
     */
    public function getChosenPaymentMethod(): ?string;
}
