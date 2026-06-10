<?php

namespace SamedayCourier\Shipping\Application\Common\Interfaces;

if (!defined('ABSPATH')) {
    exit;
}

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
