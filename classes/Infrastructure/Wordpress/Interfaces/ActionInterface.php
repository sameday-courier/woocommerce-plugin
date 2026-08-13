<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces;

interface ActionInterface
{
    /**
     * @return string
     */
    public function getActionName(): string;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @return array|null
     */
    public function getParams(): ?array;

    /**
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void;
}