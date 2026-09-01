<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Interfaces;

interface ResponseInterface
{
    /**
     * @return string
     */
    public function getNoticeMessage(): string;

    /**
     * @return bool
     */
    public function hasError(): bool;
}
