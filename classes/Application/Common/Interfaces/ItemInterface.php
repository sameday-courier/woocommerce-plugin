<?php

namespace SamedayCourier\Shipping\Application\Common\Interfaces;

if (!defined('ABSPATH')) {
    exit;
}

interface ItemInterface
{
    /**
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self;
}
