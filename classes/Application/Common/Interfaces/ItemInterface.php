<?php

namespace SamedayCourier\Shipping\Application\Common\Interfaces;

interface ItemInterface
{
    /**
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self;
}
