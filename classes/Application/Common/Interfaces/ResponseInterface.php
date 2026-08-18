<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Interfaces;

interface ResponseInterface
{
    /**
     * @return string
     */
    public function getNoticeType(): string;

    /**
     * @return string|null
     */
    public function getNoticeMessage(): ?string;

    /**
     * @return bool
     */
    public function hasNotices(): bool;
}
