<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces;

if (!defined('ABSPATH')) {
    exit;
}

interface FilterInterface
{
    /**
     * @return string
     */
    public function getFilterName(): string;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @return array|null
     */
    public function getParams(): ?array;

    /**
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function handle(...$args);
}
